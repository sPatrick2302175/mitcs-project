<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Support\Facades\DB;
use App\Models\LeaveRequestDetail;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use setasign\Fpdi\Fpdi;

class LeaveRequestController extends Controller
{
    /**
     * Display the employee's leave dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'You do not have an employee profile linked to your account yet.');
        }

        $leaveRequests = LeaveRequest::where('employee_id', $employee->id)
                                     ->orderBy('created_at', 'desc')
                                     ->get();

        $currentYear = date('Y');
        $apiHolidays = $this->getPhilippineHolidays($currentYear);

        $calendarEvents = [];
        foreach ($apiHolidays as $holiday) {
            $calendarEvents[] = [
                'title' => $holiday['name'],
                'start' => $holiday['date'],
                'backgroundColor' => '#3b82f6', 
                'borderColor' => '#2563eb',
                'textColor' => '#ffffff',
                'allDay' => true,
                'display' => 'block'
            ];
        }

        return view('leave_requests.index', compact('employee', 'leaveRequests', 'calendarEvents'));
    }

    /**
     * Show the form for creating a new leave application.
     */
    public function create()
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'You do not have an employee profile linked to your account yet.');
        }
        
        $myLeaves = LeaveRequest::where('employee_id', $employee->id)
                                ->whereIn('status', ['pending', 'approved', 'PENDING', 'APPROVED'])
                                ->get();
        $myBookedDates = [];
        foreach ($myLeaves as $leave) {
            $period = CarbonPeriod::create($leave->start_date, $leave->end_date);
            foreach ($period as $date) { $myBookedDates[] = $date->format('Y-m-d'); }
        }

        $companyApproved = LeaveRequest::where('employee_id', '!=', $employee->id)
                                       ->whereIn('status', ['approved', 'APPROVED'])
                                       ->get();
        $companyApprovedDates = [];
        foreach ($companyApproved as $leave) {
            $period = CarbonPeriod::create($leave->start_date, $leave->end_date);
            foreach ($period as $date) { $companyApprovedDates[] = $date->format('Y-m-d'); }
        }

        $companyPending = LeaveRequest::where('employee_id', '!=', $employee->id)
                                      ->whereIn('status', ['pending', 'PENDING'])
                                      ->get();
        $companyPendingDates = [];
        foreach ($companyPending as $leave) {
            $period = CarbonPeriod::create($leave->start_date, $leave->end_date);
            foreach ($period as $date) { $companyPendingDates[] = $date->format('Y-m-d'); }
        }

        $currentYear = date('Y');
        $apiHolidays = $this->getPhilippineHolidays($currentYear);
        $holidayDates = array_column($apiHolidays, 'date'); 

        $disabledDates = array_values(array_unique(array_merge($myBookedDates, $companyApprovedDates, $holidayDates)));

        return view('leave_requests.create', compact('disabledDates', 'companyApprovedDates', 'companyPendingDates'));
    }

    /**
     * Store a newly created leave request in storage.
     */
    public function store(Request $request)
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'Employee profile missing.');
        }

        $validated = $request->validate([
            'leave_type' => 'required|string',
            'leave_type_others' => 'nullable|string|required_if:leave_type,Others',
            'leave_detail_category' => 'nullable|string',
            'leave_detail_specifics' => 'nullable|string',
            'working_days_applied' => 'required|numeric|min:0.5',
            'selected_dates' => 'required|string', // Validating the comma-separated date string
            'commutation_requested' => 'required|boolean',
        ]);

        // 1. Process and Clean Dates String into an Ordered Array
        $rawDates = array_map('trim', explode(',', $validated['selected_dates']));
        sort($rawDates); // Chronological ordering
        
        // Extrapolate master table fallbacks for index displays
        $masterStartDate = Carbon::parse($rawDates[0])->startOfDay();
        $masterEndDate = Carbon::parse(end($rawDates))->startOfDay();

        // 2. Personal Overlap Check (Inspecting Specific Picked Days against DB Details)
        $myOverlap = LeaveRequestDetail::whereIn('leave_date', $rawDates)
            ->whereHas('leaveRequest', function ($query) use ($employee) {
                $query->where('employee_id', $employee->id)
                    ->whereIn('status', ['pending', 'approved', 'PENDING', 'APPROVED']);
            })->exists();

        if ($myOverlap) {
            return back()->withInput()->withErrors([
                'selected_dates' => 'You have already booked a leave request for one or more of these specific dates.'
            ]);
        }

        // 3. Company-Wide Overlap Check
        $companyOverlap = LeaveRequestDetail::whereIn('leave_date', $rawDates)
            ->whereHas('leaveRequest', function ($query) use ($employee) {
                $query->where('employee_id', '!=', $employee->id)
                    ->whereIn('status', ['approved', 'APPROVED']);
            })->exists();

        if ($companyOverlap) {
            return back()->withInput()->withErrors([
                'selected_dates' => 'One or more selected dates are already taken by another employee whose leave is approved.'
            ]);
        }

        // 4. Employee Balance Verification
        $daysApplied = $validated['working_days_applied'];
        $leaveType = $validated['leave_type'];
        $balanceAvailable = 0;

        switch ($leaveType) {
            case 'Vacation Leave':
                $balanceAvailable = $employee->vacation_leave_balance;
                break;
            case 'Sick Leave':
                $balanceAvailable = $employee->sick_leave_balance;
                break;
            case 'Mandatory/Forced Leave':
                $balanceAvailable = $employee->mandatory_leave_balance;
                break;
            case 'Special Privilege Leave':
                $balanceAvailable = $employee->special_privilege_leave_balance;
                break;
            case 'Special Emergency Leave':
                $balanceAvailable = $employee->special_emergency_leave_balance;
                break;
        }

        $trackedLeaves = [
            'Vacation Leave', 'Sick Leave', 'Mandatory/Forced Leave', 
            'Special Privilege Leave', 'Special Emergency Leave'
        ];
        
        if (in_array($leaveType, $trackedLeaves) && $balanceAvailable < $daysApplied) {
            return back()->withInput()->withErrors([
                'working_days_applied' => "Insufficient balance. You only have {$balanceAvailable} days left for {$leaveType}."
            ]);
        }

        // 5. Holiday Deductions Processing
        $startYear = $masterStartDate->year;
        $endYear = $masterEndDate->year;
        
        $apiHolidays = $this->getPhilippineHolidays($startYear);
        if ($startYear !== $endYear) {
            $apiHolidays = array_merge($apiHolidays, $this->getPhilippineHolidays($endYear));
        }

        $holidayDateStrings = array_map(function($holiday) {
            return Carbon::parse($holiday['date'])->format('Y-m-d');
        }, $apiHolidays);

        // Filter array to count valid business days and build structural records
        $validWorkingDays = 0;
        $detailsToInsert = [];

        foreach ($rawDates as $dateString) {
            $date = Carbon::parse($dateString);
            
            if ($date->isWeekday() && !in_array($dateString, $holidayDateStrings)) {
                $validWorkingDays++;
                
                $detailsToInsert[] = [
                    'leave_date' => $dateString,
                    'day_fraction' => 1.00, // Matches your premium schema default
                    'is_with_pay' => true,  // Matches your premium schema default
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if ($daysApplied > $validWorkingDays) {
            return back()->withInput()->withErrors([
                'working_days_applied' => "Error: You applied for {$daysApplied} days, but you only selected {$validWorkingDays} valid working days (excluding weekends/holidays)."
            ]);
        }

        // 6. Database Insertion Transaction Execution
        $validated['employee_id'] = $employee->id;
        $validated['date_of_filing'] = now();
        $validated['status'] = 'pending';
        
        // Inject structural start and end ranges so legacy summary scripts don't crash
        $validated['start_date'] = $masterStartDate->format('Y-m-d');
        $validated['end_date'] = $masterEndDate->format('Y-m-d');

        DB::transaction(function () use ($validated, $detailsToInsert) {
            // Drop the array payload item string before saving to master schema table
            unset($validated['selected_dates']); 
            
            $leaveRequest = LeaveRequest::create($validated);

            // Map parent ID across internal elements
            foreach ($detailsToInsert as &$detail) {
                $detail['leave_request_id'] = $leaveRequest->id;
            }

            if (!empty($detailsToInsert)) {
                LeaveRequestDetail::insert($detailsToInsert);
            }
        });

        return redirect()->route('leave-requests.index')
                        ->with('success', 'Leave application submitted successfully!');
    }

    /**
     * List all leave applications for Admin review.
     */
    public function adminIndex()
    {
        $loggedInAdmin = auth()->user();

        if ($loggedInAdmin->is_admin === User::ROLE_SUPER_ADMIN) {
            $leaveRequests = LeaveRequest::with('employee.department')
                ->orderBy('created_at', 'desc')
                ->get();
        } else { 
            // FIXED: Changed corrupted "compression:" label to a proper legal "else" block
            $departmentId = $loggedInAdmin->employee ? $loggedInAdmin->employee->department_id : null;

            $leaveRequests = LeaveRequest::whereHas('employee', function($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })->with('employee.department')->orderBy('created_at', 'desc')->get();
        }

        return view('leave_requests.admin_index', compact('leaveRequests'));
    }

    /**
     * Show the approval/review form for a specific request.
     */
    public function review($id)
    {
        $leaveRequest = LeaveRequest::with('employee')->findOrFail($id);
        return view('leave_requests.review', compact('leaveRequest'));
    }

    /**
     * Process the final Approval or Disapproval.
     */
    public function action(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        $employee = Employee::findOrFail($leaveRequest->employee_id);

        $request->validate([
            'status' => 'required|in:approved,disapproved',
            'recommendation_reason' => 'nullable|string',
            'days_with_pay' => 'nullable|numeric|min:0',
            'days_without_pay' => 'nullable|numeric|min:0',
            'disapproval_reason' => 'nullable|string',
        ]);

        $status = $request->input('status');

        if ($status === 'approved') {
            // FIXED: Deduct based on the verified input field 'days_with_pay' instead of the original 'working_days_applied'
            $daysToDeduct = (float) $request->input('days_with_pay', $leaveRequest->working_days_applied);
            
            switch ($leaveRequest->leave_type) {
                case 'Vacation Leave':
                    $employee->vacation_leave_balance -= $daysToDeduct;
                    break;
                case 'Sick Leave':
                    $employee->sick_leave_balance -= $daysToDeduct;
                    break;
                case 'Mandatory/Forced Leave':
                    $employee->mandatory_leave_balance -= $daysToDeduct;
                    break;
                case 'Special Privilege Leave':
                    $employee->special_privilege_leave_balance -= $daysToDeduct;
                    break;
                case 'Special Emergency Leave':
                    $employee->special_emergency_leave_balance -= $daysToDeduct;
                    break;
            }
            $employee->save();
        }

        $leaveRequest->update([
            'status' => $status,
            'recommendation_reason' => $request->input('recommendation_reason'),
            'days_with_pay' => $request->input('days_with_pay', 0),
            'days_without_pay' => $request->input('days_without_pay', 0),
            'disapproval_reason' => $request->input('disapproval_reason'),
            'recommending_officer_id' => Auth::id(),
            'approving_official_id' => Auth::id(),
        ]);

        return redirect()->route('admin.leave-requests.index')
                         ->with('success', 'Leave application has been processed successfully!');
    }

    /**
     * Generate and download a PDF of the Leave Request using FPDI.
     */
    public function generatePDF(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::with('employee.department')->findOrFail($id);
        $user = Auth::user();

        // Authorization check
        if (!$user->is_admin && $user->employee && $leaveRequest->employee_id !== $user->employee->id) {
            abort(403, 'Unauthorized action. You can only download your own leave forms.');
        }

        // 1. Tell FPDF where to look for your font files BEFORE initializing!
        if (!defined('FPDF_FONTPATH')) {
            define('FPDF_FONTPATH', public_path('fonts/'));
        }

        // Initialize FPDI
        $pdf = new Fpdi();

        // Add the custom fonts (assuming the same Family Name for all)
        $pdf->AddFont('CenturyGothic', '', 'gothic.php');
        $pdf->AddFont('CenturyGothic', 'B', 'gothicb.php');
        $pdf->AddFont('CenturyGothic', 'I', 'gothici.php');
        $pdf->AddFont('CenturyGothic', 'BI', 'gothicbi.php');

        // Now you can freely switch between them!
        $pdf->SetFont('CenturyGothic', '', 10);  // Regular
        $pdf->SetFont('CenturyGothic', 'B', 10); // Bold
        $pdf->SetFont('CenturyGothic', 'I', 10); // Italic
        $pdf->SetFont('CenturyGothic', 'BI', 10);// Bold Italic

        // 1. Set the path to your blank official PDF template
        // Make sure you place your blank PDF in the storage/app/templates folder!
        $templatePath = storage_path('app/templates/CSC_Form_6_Template.pdf'); 
        
        // Get the page count of the template
        $pageCount = $pdf->setSourceFile($templatePath);

        // --- PAGE 1: FRONT PAGE (Fill in the data) ---
        $page1Id = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($page1Id);
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($page1Id);

        // Set default font (Arial, regular, 10pt)
        $pdf->SetFont('CenturyGothic', 'B', 8);
        $pdf->SetTextColor(0, 0, 0); // Black text

        // --- TOP SECTION ---
        $department = $leaveRequest->employee->department->code ?? 'MITCS';
        $pdf->SetXY(30, 40); 
        $pdf->Write(0, $department);

        $pdf->SetXY(90, 40);
        $pdf->Write(0, mb_strtoupper($leaveRequest->employee->last_name, 'UTF-8'));

        $pdf->SetXY(120, 40);
        $pdf->Write(0, mb_strtoupper($leaveRequest->employee->first_name, 'UTF-8'));

        $pdf->SetXY(157, 40);
        $mi = $leaveRequest->employee->middle_initial ?? '';
        $formatted_mi = !empty($mi) ? mb_strtoupper($mi, 'UTF-8') . '.' : '';
        $pdf->Write(0, $formatted_mi);

        $pdf->SetXY(37, 47);
        $pdf->Write(0, \Carbon\Carbon::parse($leaveRequest->date_of_filing)->format('M d, Y'));

        $pdf->SetXY(97, 47);
        $pdf->Write(0, mb_strtoupper($leaveRequest->employee->position_code, 'UTF-8'));

        //SALARY
        /*$pdf->SetXY(97, 47);
        $pdf->Write(0, $leaveRequest->salary->position);*/

        /// --- LEAVE TYPE CHECKBOXES (Using Checkmark) ---
        // 1. Define your layout data (Lookup Dictionary) y = +5.2 to each
        $leaveYPositions = [
            'Vacation Leave'                   => 68.2,
            'Mandatory/Forced Leave'           => 73.4,
            'Sick Leave'                       => 78.6,
            'Maternity Leave'                  => 83.8,
            'Paternity Leave'                  => 89,
            'Special Privilege Leave'          => 94.2,
            'Solo Parent Leave'                => 99.4,
            'Study Leave'                      => 104.6,
            '10-Day VAWC Leave'                => 109.7,
            'Rehabilitation Privilege'         => 114.8,
            'Special Leave Benefits for Women' => 120,
            'Special Emergency Leave'          => 125.2,
            'Adoption Leave'                   => 130.2,
        ];

        $type = $leaveRequest->leave_type;

        // 2. Handle the specific "Others" logic
        if ($type === 'Others') {
            $pdf->SetFont('CenturyGothic', '', 10);
            $pdf->SetXY(40, 196);
            $pdf->Write(0, $leaveRequest->leave_type_others);
        } 
        // 3. Handle standard checkboxes dynamically
        elseif (array_key_exists($type, $leaveYPositions)) {
            $pdf->SetXY(6, $leaveYPositions[$type]); 
            
            // Render the checkmark
            $pdf->SetFont('zapfdingbats', '', 8); 
            $pdf->Write(0, '3'); 
            
            // Reset font back to default
            $pdf->SetFont('CenturyGothic', '', 10); 
        }

        // --- LEAVE DETAILS (Category & Specifics) ---
       // --- LEAVE DETAILS (Category & Specifics) ---
        $detailYPositions = [
            'Within the Philippines' => 74,
            'Abroad'                 => 79.2,
            'In Hospital'            => 89.6,
            'Out Patient'            => 94.8,//missing others
        ];

        $category = $leaveRequest->leave_detail_category;

        if (array_key_exists($category, $detailYPositions)) {
            $y = $detailYPositions[$category];

            // 1. Render the checkmark symbol
            $pdf->SetXY(117.8, $y);
            $pdf->SetFont('zapfdingbats', '', 8); // Using size 8 to match your checkboxes
            $pdf->Write(0, '3');

            // 2. Render the specific details text next to it
            $pdf->SetFont('CenturyGothic', '', 10);
            $pdf->SetXY(150, $y);
            $pdf->Write(0, $leaveRequest->leave_detail_specifics);
        }

        // --- DAYS AND DATES ---
        $pdf->SetFont('CenturyGothic', '', 10);
        $pdf->SetXY(30, 215);
        $pdf->Write(0, number_format($leaveRequest->working_days_applied, 1) . ' days');

        $pdf->SetXY(30, 230);
        $dates = \Carbon\Carbon::parse($leaveRequest->start_date)->format('M d, Y') . ' - ' . \Carbon\Carbon::parse($leaveRequest->end_date)->format('M d, Y');
        $pdf->Write(0, $dates);

        // --- COMMUTATION ---
        // If requested, Y = 222. If not requested, Y = 215.
        $y = $leaveRequest->commutation_requested ? 222 : 215;

        $pdf->SetXY(120, $y);

        // Temporarily switch to ZapfDingbats to render the checkmark
        $pdf->SetFont('zapfdingbats', '', 8); 
        $pdf->Write(0, '3'); 

        // Reset back to your default font
        $pdf->SetFont('CenturyGothic', '', 10);

        // --- PAGE 2: BACK PAGE (Instructions - Blank) ---
        if ($pageCount > 1) {
            $page2Id = $pdf->importPage(2);
            $size2 = $pdf->getTemplateSize($page2Id);
            $pdf->AddPage($size2['orientation'], [$size2['width'], $size2['height']]);
            $pdf->useTemplate($page2Id);
        }

       // --- OUTPUT ---
        $startDateStr = $leaveRequest->start_date instanceof \Carbon\Carbon 
            ? $leaveRequest->start_date->format('Ymd') 
            : \Carbon\Carbon::parse($leaveRequest->start_date)->format('Ymd');

        $fileName = 'CSC_Form_6_' . $leaveRequest->employee->last_name . '_' . $startDateStr . '.pdf';
        
        // If the URL has ?download=1, force the download. Otherwise, view in browser.
        if ($request->has('download')) {
            $pdf->Output('D', $fileName);
        } else {
            $pdf->Output('I', $fileName);
        }
        
        exit;
    }

    /**
     * Helper method to fetch and cache Philippine Holidays from an external API.
     */
    private function getPhilippineHolidays($year)
    {
        return Cache::remember("api_ph_holidays_{$year}", now()->addDays(30), function () use ($year) {
            try {
                $response = Http::timeout(5)->get("https://date.nager.at/api/v3/PublicHolidays/{$year}/PH");
                
                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Exception $e) {
                // Fail gracefully and return empty array if offline
            }
            
            return [];
        });
    }
}