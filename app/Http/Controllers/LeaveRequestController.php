<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

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
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'commutation_requested' => 'required|boolean',
        ]);

        $myOverlap = LeaveRequest::where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'approved', 'PENDING', 'APPROVED'])
            ->where('start_date', '<=', $validated['end_date'])
            ->where('end_date', '>=', $validated['start_date'])
            ->exists();

        if ($myOverlap) {
            return back()->withInput()->withErrors([
                'start_date' => 'You have already booked a leave request during these dates.'
            ]);
        }

        $companyOverlap = LeaveRequest::where('employee_id', '!=', $employee->id)
            ->whereIn('status', ['approved', 'APPROVED'])
            ->where('start_date', '<=', $validated['end_date'])
            ->where('end_date', '>=', $validated['start_date'])
            ->exists();

        if ($companyOverlap) {
            return back()->withInput()->withErrors([
                'start_date' => 'One or more selected dates are already taken by another employee whose leave is approved.'
            ]);
        }

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

        $start = Carbon::parse($validated['start_date'])->startOfDay();
        $end = Carbon::parse($validated['end_date'])->startOfDay();
        
        $workingDays = $start->diffInDaysFiltered(function (Carbon $date) {
            return $date->isWeekday();
        }, $end->copy()->addDay());

        $startYear = $start->year;
        $endYear = $end->year;
        
        $apiHolidays = $this->getPhilippineHolidays($startYear);
        if ($startYear !== $endYear) {
            $apiHolidays = array_merge($apiHolidays, $this->getPhilippineHolidays($endYear));
        }

        $holidayCount = 0;
        foreach ($apiHolidays as $holiday) {
            $holidayDate = Carbon::parse($holiday['date']);
            if ($holidayDate->between($start, $end) && $holidayDate->isWeekday()) {
                $holidayCount++;
            }
        }

        $maxValidWorkingDays = $workingDays - $holidayCount;
        
        if ($daysApplied > $maxValidWorkingDays) {
             return back()->withInput()->withErrors([
                 'working_days_applied' => "Error: You applied for {$daysApplied} days, but there are only {$maxValidWorkingDays} valid working days in this range (excluding weekends and holidays)."
             ]);
        }

        $validated['employee_id'] = $employee->id;
        $validated['date_of_filing'] = now();
        $validated['status'] = 'pending';

        LeaveRequest::create($validated);

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
     * Generate and download a PDF of the Leave Request.
     */
    public function generatePDF($id)
    {
        $leaveRequest = LeaveRequest::with('employee')->findOrFail($id);
        $user = Auth::user();

        if (!$user->is_admin && $user->employee && $leaveRequest->employee_id !== $user->employee->id) {
            abort(403, 'Unauthorized action. You can only download your own leave forms.');
        }

        $pdf = Pdf::loadView('leave_requests.pdf', compact('leaveRequest'));
        $pdf->setPaper('A4', 'portrait');

        $startDateStr = $leaveRequest->start_date instanceof Carbon 
            ? $leaveRequest->start_date->format('Ymd') 
            : Carbon::parse($leaveRequest->start_date)->format('Ymd');

        $fileName = 'CSC_Form_6_' . $leaveRequest->employee->last_name . '_' . $startDateStr . '.pdf';
        return $pdf->download($fileName);
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