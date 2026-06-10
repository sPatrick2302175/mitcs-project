<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\Employee;
use App\Models\User;
use App\Models\CustomHoliday;
use App\Models\Department;
use App\Models\Division;
use App\Http\Requests\StoreLeaveRequest;
use App\Http\Requests\ProcessLeaveActionRequest;
use App\Services\LeaveManagementService;
use App\Services\LeaveFormService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    protected $leaveService;
    protected $pdfService;

    public function __construct(LeaveManagementService $leaveService, LeaveFormService $pdfService)
    {
        $this->leaveService = $leaveService;
        $this->pdfService = $pdfService;
    }

    public function index()
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        $leaveRequests = $employee 
            ? LeaveRequest::with('details')
                ->where('employee_id', $employee->id)
                ->where('created_at', '>=', now()->subDays(30))
                ->latest()
                ->get() 
            : collect();

        // Build corporate leaves query based on user role
        $leaveQuery = LeaveRequest::with(['employee', 'details'])
            ->whereIn('status', ['pending', 'approved']);

        if ($user->role !== 'admin' && $employee?->division_id) {
            $leaveQuery->whereHas('employee', function ($query) use ($employee) {
                $query->where('division_id', $employee->division_id);
            });
        }

        $allLeaves = $leaveQuery->get();

        // Merge calendar events using our new helper methods
        $calendarEvents = array_merge(
            $this->getHolidayEvents(),
            $this->getLeaveEvents($allLeaves, $employee?->id, false)
        );

        return view('leave_requests.index', compact('employee', 'leaveRequests', 'calendarEvents'));
    }

    public function create()
    {
        $employee = Auth::user()->employee;
        
        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'You do not have an employee profile linked to your account yet.');
        }
        
        $myBookedDates = $this->leaveService->getBookedDates(
            LeaveRequest::where('employee_id', $employee->id)->whereIn('status', ['pending', 'approved'])
        );

        $divisionQuery = LeaveRequest::whereHas('employee', function($q) use ($employee) {
            $q->where('division_id', $employee->division_id)->where('id', '!=', $employee->id);
        });

        $divisionApprovedDates = $this->leaveService->getBookedDates((clone $divisionQuery)->where('status', 'approved'));
        $divisionPendingDates = $this->leaveService->getBookedDates((clone $divisionQuery)->where('status', 'pending'));
        
        $holidayDates = array_column($this->leaveService->getPhilippineHolidays(date('Y')), 'date'); 
        
        $disabledDates = array_values(array_unique(array_merge(
            $myBookedDates, 
            $divisionApprovedDates, 
            $divisionPendingDates, 
            $holidayDates
        )));

        return view('leave_requests.create', compact('disabledDates', 'divisionApprovedDates', 'divisionPendingDates', 'myBookedDates'));
    }

    public function store(StoreLeaveRequest $request)
    {
        $employee = Auth::user()->employee;
        if (!$employee) return redirect()->route('dashboard')->with('error', 'Employee profile missing.');

        $validated = $request->validated();
        $rawDates = array_map('trim', explode(',', $validated['selected_dates']));
        sort($rawDates);
        
        if ($this->leaveService->checkPersonalOverlap($employee, $rawDates)) {
            return back()->withInput()->withErrors(['selected_dates' => 'You have already booked a leave request for one or more of these specific dates.']);
        }

        if ($this->leaveService->checkDivisionOverlap($employee, $rawDates)) {
            return back()->withInput()->withErrors(['selected_dates' => 'One or more selected dates are already taken by another employee whose leave is approved.']);
        }

        $balanceField = $this->leaveService->getBalanceField($validated['leave_type']);
        if ($balanceField && $employee->$balanceField < $validated['working_days_applied']) {
            return back()->withInput()->withErrors(['working_days_applied' => "Insufficient balance. You only have {$employee->$balanceField} days left."]);
        }

        $startYear = Carbon::parse($rawDates[0])->year;
        $endYear = Carbon::parse(end($rawDates))->year;
        $holidayData = $startYear === $endYear 
            ? $this->leaveService->getPhilippineHolidays($startYear)
            : array_merge($this->leaveService->getPhilippineHolidays($startYear), $this->leaveService->getPhilippineHolidays($endYear));
            
        $holidayStrings = array_map(fn($h) => Carbon::parse($h['date'])->format('Y-m-d'), $holidayData);

        $validWorkingDays = 0;
        $detailsToInsert = [];
        
        foreach ($rawDates as $dateString) {
            if (Carbon::parse($dateString)->isWeekday() && !in_array($dateString, $holidayStrings)) {
                $validWorkingDays++;
                $detailsToInsert[] = ['leave_date' => $dateString, 'day_fraction' => 1.00, 'is_with_pay' => true, 'created_at' => now(), 'updated_at' => now()];
            }
        }

        if ($validated['working_days_applied'] > $validWorkingDays) {
            return back()->withInput()->withErrors(['working_days_applied' => "Error: You applied for {$validated['working_days_applied']} days, but you only selected {$validWorkingDays} valid working days."]);
        }

        $this->leaveService->processLeaveTransaction($validated, $employee, $rawDates, $detailsToInsert);

        return redirect()->route('leave-requests.index')->with('success', 'Leave application submitted successfully!');
    }

    public function adminIndex(Request $request)
    {
        $admin = auth()->user();
        $query = LeaveRequest::with('employee.department', 'employee.division', 'details')->latest();

        if ($admin->is_admin === User::ROLE_SUPER_ADMIN) {
            $query->whereHas('employee.department', fn($q) => $q->where('code', '!=', 'SYSTEM-ADMIN'));
            $validDeptIds = Department::where('code', '!=', 'SYSTEM-ADMIN')->pluck('id');
            $divisions = Division::whereIn('department_id', $validDeptIds)->get();
        } else {
            $deptId = $admin->employee?->department_id;
            $query->whereHas('employee', fn($q) => $q->where('department_id', $deptId));
            $divisions = Division::where('department_id', $deptId)->get();
        }

        $query->when($request->search, function ($q, $search) {
            $q->where(function ($subQ) use ($search) {
                $subQ->where('leave_type', 'like', "%{$search}%")
                     ->orWhereHas('employee', fn($empQ) => $empQ->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]));
            });
        })
        ->when($request->status, fn($q, $status) => $q->where('status', $status))
        ->when($request->division, fn($q, $division) => $q->whereHas('employee', fn($empQ) => $empQ->where('division_id', $division)));

        $allLeaves = $query->get();
        
        $calendarEvents = array_merge(
            $this->getHolidayEvents(),
            $this->getLeaveEvents($allLeaves, null, true)
        );

        return view('leave_requests.admin_index', [
            'leaveRequests' => $query->paginate(10),
            'calendarEvents' => $calendarEvents,
            'divisions' => $divisions 
        ]);
    }

    public function show($id)
    {
        $leaveRequest = LeaveRequest::with('employee.department')->findOrFail($id);
        $user = Auth::user();

        if (!$user->is_admin && $user->employee && $leaveRequest->employee_id !== $user->employee->id) {
            abort(403, 'Unauthorized action. You can only view your own leave requests.');
        }

        return view('leave_requests.review', compact('leaveRequest'));
    }

    public function review($id)
    {
        return view('leave_requests.review', ['leaveRequest' => LeaveRequest::with('employee')->findOrFail($id)]);
    }

    public function action(ProcessLeaveActionRequest $request, $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        $employee = Employee::findOrFail($leaveRequest->employee_id);
        $status = strtolower($request->status);

        if ($status === 'approved') {
            $daysToDeduct = (float) $request->input('days_with_pay', $leaveRequest->working_days_applied);
            $this->leaveService->deductEmployeeBalance($employee, $leaveRequest->leave_type, $daysToDeduct);
        }

        $leaveRequest->update([
            'status' => $status,
            'recommendation_reason' => $request->recommendation_reason,
            'days_with_pay' => $request->input('days_with_pay', 0),
            'days_without_pay' => $request->input('days_without_pay', 0),
            'disapproval_reason' => $request->disapproval_reason,
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
            abort(403, 'Unauthorized action.');
        }

        return $this->pdfService->generate($leaveRequest, $request);
    }

    public function myHistory(Request $request)
    {
        $employee = auth()->user()->employee; 

        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'You must have a linked employee profile to view history.');
        }

        $leaveRequests = LeaveRequest::where('employee_id', $employee->id)
            ->when($request->search, function($q, $search) {
                $q->where(function($subQ) use ($search) {
                    $subQ->where('leave_type', 'like', "%{$search}%")
                         ->orWhere('leave_type_others', 'like', "%{$search}%")
                         ->orWhere('leave_detail_category', 'like', "%{$search}%")
                         ->orWhere('leave_detail_specifics', 'like', "%{$search}%");
                });
            })
            ->when($request->timeframe, function($q, $timeframe) {
                match ($timeframe) {
                    'this_month' => $q->whereMonth('date_of_filing', now()->month)->whereYear('date_of_filing', now()->year),
                    'last_3_months' => $q->where('date_of_filing', '>=', now()->subMonths(3)),
                    'this_year' => $q->whereYear('date_of_filing', now()->year),
                    default => $q
                };
            })
            ->when($request->status, fn($q, $status) => $q->where('status', strtolower($status)))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('leave_requests.history', compact('leaveRequests'));
    }

    // ==========================================
    // PRIVATE HELPER METHODS
    // ==========================================

    private function getHolidayEvents(): array
    {
        return CustomHoliday::all()->map(function ($holiday) {
            $isRegular = ($holiday->type === 'regular'); 
            return [
                'id' => 'custom_' . $holiday->id,
                'title' => $holiday->name . ($holiday->is_half_day ? ' (Half-Day)' : ''),
                'start' => $holiday->date,
                'backgroundColor' => $isRegular ? '#3b82f6' : '#f97316',
                'borderColor' => $isRegular ? '#2563eb' : '#ea580c',
                'textColor' => '#ffffff',
                'allDay' => true,
                'extendedProps' => ['type' => 'custom_holiday', 'holiday_id' => $holiday->id]
            ];
        })->toArray();
    }

    private function getLeaveEvents($leaves, $myEmployeeId, $isAdminView): array
    {
        $events = [];

        foreach ($leaves as $leave) {
            $status = strtolower($leave->status);

            // Skip disapproved leaves entirely so they aren't added to the calendar array
            if ($status === 'disapproved' || $status === 'rejected') {
                continue; 
            }

            $isPending = $status === 'pending';
            
            if ($isAdminView) {
                $bgColor = $isPending ? '#fff2cb' : ($status === 'approved' ? '#c1f7d5' : '#ffffff');
                $bdColor = $isPending ? '#ca8a04' : ($status === 'approved' ? '#16a34a' : '#dc2626');
                $textColor = '#1a8026'; // Custom text color for admin
            } else {
                $isMyLeave = ($leave->employee_id === $myEmployeeId);
                $bgColor = $isMyLeave ? ($isPending ? '#eab308' : '#22c55e') : ($isPending ? '#94a3b8' : '#64748b');
                $bdColor = $isMyLeave ? ($isPending ? '#ca8a04' : '#16a34a') : ($isPending ? '#64748b' : '#475569');
                $textColor = '#ffffff';
            }

            $employeeName = $leave->employee->first_name ?? 'Employee';
            $title = $isAdminView ? "$employeeName - {$leave->leave_type}" : "$employeeName ({$leave->leave_type})";

            foreach ($leave->details as $detail) {
                $events[] = [
                    'title' => $title,
                    'start' => $detail->leave_date->format('Y-m-d'), 
                    'backgroundColor' => $bgColor,
                    'borderColor' => $bdColor,
                    'textColor' => $textColor,
                    'allDay' => true,
                    'extendedProps' => [
                        'type' => $isAdminView ? 'leave_request' : ($isPending ? 'pending_leave' : 'approved_leave'),
                        'leave_id' => $leave->id,
                        'detail_id' => $detail->id ?? null,
                        'status' => $status
                    ]
                ];
            }
        }

        return $events;
    }
}