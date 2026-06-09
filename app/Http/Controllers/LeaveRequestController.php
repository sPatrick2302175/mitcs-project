<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\Employee;
use App\Models\User;
use App\Models\CustomHoliday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use setasign\Fpdi\Fpdi;

class LeaveRequestController extends Controller
{
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

        $calendarEvents = [];

        // Generate Regular Holidays for this year, next year, and 2026 to ensure the calendar works perfectly
        $this->ensureRegularHolidaysExist(date('Y'));
        $this->ensureRegularHolidaysExist(date('Y') + 1);
        $this->ensureRegularHolidaysExist(2026);

        // 1. ALL Holidays (Now pulled 100% locally from your database)
        $holidays = CustomHoliday::all();
        foreach ($holidays as $holiday) {
            $isRegular = ($holiday->type === 'regular'); // Check the type
            
            // Append (Half-Day) label to visual indicator if applicable
            $displayTitle = $holiday->name . ($holiday->is_half_day ? ' (Half-Day)' : '');

            $calendarEvents[] = [
                'id' => 'custom_'.$holiday->id,
                'title' => $displayTitle,
                'start' => $holiday->date,
                'backgroundColor' => $isRegular ? '#3b82f6' : '#f97316', // Blue for Regular, Orange for Custom
                'borderColor' => $isRegular ? '#2563eb' : '#ea580c',
                'textColor' => '#ffffff',
                'allDay' => true,
                'extendedProps' => ['type' => 'custom_holiday', 'holiday_id' => $holiday->id]
            ];
        }

        // 2. Corporate Leaves (YELLOW for Pending, GREEN for Approved)
        $allLeaves = LeaveRequest::with('employee')->whereIn('status', ['pending', 'approved'])->get();
        foreach ($allLeaves as $leave) {
            $isPending = ($leave->status === 'pending');
            $bgColor = $isPending ? '#eab308' : '#22c55e'; // Yellow : Green
            $bdColor = $isPending ? '#ca8a04' : '#16a34a';

            // FullCalendar end dates are exclusive, so we add 1 day to render correctly
            $endDate = Carbon::parse($leave->end_date)->addDay()->format('Y-m-d');

            $employeeName = $leave->employee ? $leave->employee->first_name : 'Employee';

            $calendarEvents[] = [
                'title' => $employeeName . ' (' . $leave->leave_type . ')',
                'start' => $leave->start_date,
                'end' => $endDate,
                'backgroundColor' => $bgColor,
                'borderColor' => $bdColor,
                'textColor' => '#ffffff',
                'allDay' => true,
                'extendedProps' => [
                    'type' => $leave->status . '_leave',
                    'leave_id' => $leave->id
                ]
            ];
        }

        return view('leave_requests.index', compact('employee', 'leaveRequests', 'calendarEvents'));
    }

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
        // Now pulls from local database instead of API
        $localHolidays = $this->getPhilippineHolidays($currentYear);
        
        // ONLY block full holidays. Half-days remain clickable on the frontend flatpickr calendar!
        $fullHolidays = array_filter($localHolidays, function($h) { 
            return empty($h['is_half_day']); 
        });
        $holidayDates = array_column($fullHolidays, 'date'); 

        $disabledDates = array_values(array_unique(array_merge($myBookedDates, $companyApprovedDates, $holidayDates)));

        return view('leave_requests.create', compact('disabledDates', 'companyApprovedDates', 'companyPendingDates'));
    }

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
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'commutation_requested' => 'required|boolean',
        ]);

        $myOverlap = LeaveRequest::where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where('start_date', '<=', $validated['end_date'])
            ->where('end_date', '>=', $validated['start_date'])
            ->exists();

        if ($myOverlap) {
            return back()->withInput()->withErrors(['start_date' => 'You have already booked a leave request during these dates.']);
        }

        $companyOverlap = LeaveRequest::where('employee_id', '!=', $employee->id)
            ->where('status', 'approved')
            ->where('start_date', '<=', $validated['end_date'])
            ->where('end_date', '>=', $validated['start_date'])
            ->exists();

        if ($companyOverlap) {
            return back()->withInput()->withErrors(['start_date' => 'One or more selected dates are already taken by another employee whose leave is approved.']);
        }

        $daysApplied = $validated['working_days_applied'];
        $leaveType = $validated['leave_type'];
        $balanceAvailable = 0;

        switch ($leaveType) {
            case 'Vacation Leave': $balanceAvailable = $employee->vacation_leave_balance; break;
            case 'Sick Leave': $balanceAvailable = $employee->sick_leave_balance; break;
            case 'Mandatory/Forced Leave': $balanceAvailable = $employee->mandatory_leave_balance; break;
            case 'Special Privilege Leave': $balanceAvailable = $employee->special_privilege_leave_balance; break;
            case 'Special Emergency Leave': $balanceAvailable = $employee->special_emergency_leave_balance; break;
        }

        $trackedLeaves = ['Vacation Leave', 'Sick Leave', 'Mandatory/Forced Leave', 'Special Privilege Leave', 'Special Emergency Leave'];
        
        if (in_array($leaveType, $trackedLeaves) && $balanceAvailable < $daysApplied) {
             return back()->withInput()->withErrors(['working_days_applied' => "Insufficient balance. You only have {$balanceAvailable} days left for {$leaveType}."]);
        }

        $start = Carbon::parse($validated['start_date'])->startOfDay();
        $end = Carbon::parse($validated['end_date'])->startOfDay();
        
        $workingDays = $start->diffInDaysFiltered(function (Carbon $date) { return $date->isWeekday(); }, $end->copy()->addDay());

        $startYear = $start->year;
        $endYear = $end->year;
        
        $localHolidays = $this->getPhilippineHolidays($startYear);
        if ($startYear !== $endYear) {
            $localHolidays = array_merge($localHolidays, $this->getPhilippineHolidays($endYear));
        }

        $holidayCount = 0;
        foreach ($localHolidays as $holiday) {
            $holidayDate = Carbon::parse($holiday['date']);
            if ($holidayDate->between($start, $end) && $holidayDate->isWeekday()) {
                // If it's a half-day, only subtract 0.5 days. Otherwise, subtract 1 full day.
                $holidayCount += empty($holiday['is_half_day']) ? 1 : 0.5;
            }
        }

        $maxValidWorkingDays = $workingDays - $holidayCount;
        
        if ($daysApplied > $maxValidWorkingDays) {
             return back()->withInput()->withErrors(['working_days_applied' => "Error: You applied for {$daysApplied} days, but there are only {$maxValidWorkingDays} valid working days in this range (excluding weekends and holidays)."]);
        }

        $validated['employee_id'] = $employee->id;
        $validated['date_of_filing'] = now();
        $validated['status'] = 'pending';

        LeaveRequest::create($validated);

        return redirect()->route('leave-requests.index')->with('success', 'Leave application submitted successfully!');
    }

    public function adminIndex()
    {
        $loggedInAdmin = auth()->user();

        if ($loggedInAdmin->is_admin === User::ROLE_SUPER_ADMIN) {
            $leaveRequests = LeaveRequest::with('employee.department')->orderBy('created_at', 'desc')->get();
        } else { 
            $departmentId = $loggedInAdmin->employee ? $loggedInAdmin->employee->department_id : null;
            $leaveRequests = LeaveRequest::whereHas('employee', function($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })->with('employee.department')->orderBy('created_at', 'desc')->get();
        }

        return view('leave_requests.admin_index', compact('leaveRequests'));
    }

    public function review($id)
    {
        $leaveRequest = LeaveRequest::with('employee')->findOrFail($id);
        return view('leave_requests.review', compact('leaveRequest'));
    }

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
            $daysToDeduct = (float) $request->input('days_with_pay', $leaveRequest->working_days_applied);
            switch ($leaveRequest->leave_type) {
                case 'Vacation Leave': $employee->vacation_leave_balance -= $daysToDeduct; break;
                case 'Sick Leave': $employee->sick_leave_balance -= $daysToDeduct; break;
                case 'Mandatory/Forced Leave': $employee->mandatory_leave_balance -= $daysToDeduct; break;
                case 'Special Privilege Leave': $employee->special_privilege_leave_balance -= $daysToDeduct; break;
                case 'Special Emergency Leave': $employee->special_emergency_leave_balance -= $daysToDeduct; break;
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

        return redirect()->route('admin.leave-requests.index')->with('success', 'Leave application has been processed successfully!');
    }

    public function generatePDF(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::with('employee.department')->findOrFail($id);
        $user = Auth::user();

        if (!$user->is_admin && $user->employee && $leaveRequest->employee_id !== $user->employee->id) {
            abort(403, 'Unauthorized action. You can only download your own leave forms.');
        }

        if (!defined('FPDF_FONTPATH')) { define('FPDF_FONTPATH', public_path('fonts/')); }

        $pdf = new Fpdi();
        $pdf->AddFont('CenturyGothic', '', 'gothic.php');
        $pdf->AddFont('CenturyGothic', 'B', 'gothicb.php');
        $pdf->AddFont('CenturyGothic', 'I', 'gothici.php');
        $pdf->AddFont('CenturyGothic', 'BI', 'gothicbi.php');

        $pdf->SetFont('CenturyGothic', '', 10);
        
        $templatePath = storage_path('app/templates/CSC_Form_6_Template.pdf'); 
        $pageCount = $pdf->setSourceFile($templatePath);

        $page1Id = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($page1Id);
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($page1Id);

        $pdf->SetFont('CenturyGothic', 'B', 8);
        $pdf->SetTextColor(0, 0, 0); 

        $department = $leaveRequest->employee->department->code ?? 'MITCS';
        $pdf->SetXY(30, 40); $pdf->Write(0, $department);
        $pdf->SetXY(90, 40); $pdf->Write(0, mb_strtoupper($leaveRequest->employee->last_name, 'UTF-8'));
        $pdf->SetXY(120, 40); $pdf->Write(0, mb_strtoupper($leaveRequest->employee->first_name, 'UTF-8'));
        
        $mi = $leaveRequest->employee->middle_initial ?? '';
        $formatted_mi = !empty($mi) ? mb_strtoupper($mi, 'UTF-8') . '.' : '';
        $pdf->SetXY(157, 40); $pdf->Write(0, $formatted_mi);

        $pdf->SetXY(37, 47); $pdf->Write(0, \Carbon\Carbon::parse($leaveRequest->date_of_filing)->format('M d, Y'));
        $pdf->SetXY(97, 47); $pdf->Write(0, mb_strtoupper($leaveRequest->employee->position, 'UTF-8'));

        $leaveYPositions = [
            'Vacation Leave' => 68.2, 'Mandatory/Forced Leave' => 73.4, 'Sick Leave' => 78.6,
            'Maternity Leave' => 83.8, 'Paternity Leave' => 89, 'Special Privilege Leave' => 94.2,
            'Solo Parent Leave' => 99.4, 'Study Leave' => 104.6, '10-Day VAWC Leave' => 109.7,
            'Rehabilitation Privilege' => 114.8, 'Special Leave Benefits for Women' => 120,
            'Special Emergency Leave' => 125.2, 'Adoption Leave' => 130.2,
        ];

        $type = $leaveRequest->leave_type;

        if ($type === 'Others') {
            $pdf->SetFont('CenturyGothic', '', 10);
            $pdf->SetXY(40, 196); $pdf->Write(0, $leaveRequest->leave_type_others);
        } elseif (array_key_exists($type, $leaveYPositions)) {
            $pdf->SetXY(6, $leaveYPositions[$type]); 
            $pdf->SetFont('zapfdingbats', '', 8); 
            $pdf->Write(0, '3'); 
        }

        $detailYPositions = ['Within the Philippines' => 110, 'Abroad' => 117, 'In Hospital' => 130, 'Out Patient' => 137];
        $category = $leaveRequest->leave_detail_category;

        if (array_key_exists($category, $detailYPositions)) {
            $y = $detailYPositions[$category];
            $pdf->SetXY(110, $y);
            $pdf->SetFont('zapfdingbats', '', 8); 
            $pdf->Write(0, '3');
            $pdf->SetFont('CenturyGothic', '', 10);
            $pdf->SetXY(150, $y);
            $pdf->Write(0, $leaveRequest->leave_detail_specifics);
        }

        $pdf->SetFont('CenturyGothic', '', 10);
        $pdf->SetXY(30, 215); $pdf->Write(0, number_format($leaveRequest->working_days_applied, 1) . ' days');
        $pdf->SetXY(30, 230); $pdf->Write(0, \Carbon\Carbon::parse($leaveRequest->start_date)->format('M d, Y') . ' - ' . \Carbon\Carbon::parse($leaveRequest->end_date)->format('M d, Y'));

        $y = $leaveRequest->commutation_requested ? 222 : 215;
        $pdf->SetXY(120, $y);
        $pdf->SetFont('zapfdingbats', '', 8); $pdf->Write(0, '3'); 

        if ($pageCount > 1) {
            $page2Id = $pdf->importPage(2);
            $size2 = $pdf->getTemplateSize($page2Id);
            $pdf->AddPage($size2['orientation'], [$size2['width'], $size2['height']]);
            $pdf->useTemplate($page2Id);
        }

        $startDateStr = $leaveRequest->start_date instanceof \Carbon\Carbon ? $leaveRequest->start_date->format('Ymd') : \Carbon\Carbon::parse($leaveRequest->start_date)->format('Ymd');
        $fileName = 'CSC_Form_6_' . $leaveRequest->employee->last_name . '_' . $startDateStr . '.pdf';
        
        if ($request->has('download')) { $pdf->Output('D', $fileName); } else { $pdf->Output('I', $fileName); }
        exit;
    }

    /**
     * Now purely fetches holidays stored by the Admin in the local database.
     * Includes the is_half_day column for accurate counting and front-end handling.
     */
    private function getPhilippineHolidays($year)
    {
        return CustomHoliday::whereYear('date', $year)
                            ->get(['name', 'date', 'is_half_day'])
                            ->toArray();
    }
    
    /**
     * Secretly generates standard PH holidays into the database if they don't exist yet.
     */
    private function ensureRegularHolidaysExist($year)
    {
        // Calculate Holy Week (Movable)
        $easterDays = easter_days($year);
        $easter = new \DateTime("$year-03-21");
        $easter->modify("+$easterDays days");

        $maundyThursday = clone $easter;
        $maundyThursday->modify('-3 days');

        $goodFriday = clone $easter;
        $goodFriday->modify('-2 days');

        $nationalHeroesDay = new \DateTime("last monday of august $year");

        $regularHolidays = [
            ['name' => "New Year's Day", 'date' => "$year-01-01"],
            ['name' => "Araw ng Kagitingan", 'date' => "$year-04-09"],
            ['name' => "Maundy Thursday", 'date' => $maundyThursday->format('Y-m-d')],
            ['name' => "Good Friday", 'date' => $goodFriday->format('Y-m-d')],
            ['name' => "Labor Day", 'date' => "$year-05-01"],
            ['name' => "Independence Day", 'date' => "$year-06-12"],
            ['name' => "National Heroes Day", 'date' => $nationalHeroesDay->format('Y-m-d')],
            ['name' => "Bonifacio Day", 'date' => "$year-11-30"],
            ['name' => "Christmas Day", 'date' => "$year-12-25"],
            ['name' => "Rizal Day", 'date' => "$year-12-30"],
        ];

        // Inject them safely into the database
        foreach ($regularHolidays as $holiday) {
            // Check if a holiday with this name ALREADY exists in this specific year
            $exists = CustomHoliday::where('name', $holiday['name'])
                                   ->whereYear('date', $year)
                                   ->exists();

            // Only create it if the admin hasn't already moved/renamed it for this year
            if (!$exists) {
                CustomHoliday::create([
                    'date' => $holiday['date'], 
                    'name' => $holiday['name'], 
                    'type' => 'regular', 
                    'is_half_day' => false
                ]);
            }
        }
    }
}