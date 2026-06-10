<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\Employee;
use App\Models\User;
use App\Http\Requests\StoreLeaveRequest;
use App\Http\Requests\ProcessLeaveActionRequest;
use App\Services\LeaveManagementService;
use App\Services\LeaveFormService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CustomHoliday;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    protected $leaveService;
    protected $pdfService;

    // Injecting services neatly through the constructor
    public function __construct(LeaveManagementService $leaveService, LeaveFormService $pdfService)
    {
        $this->leaveService = $leaveService;
        $this->pdfService = $pdfService;
    }

    public function index()
    {
        $employee = Auth::user()->employee;
        
        // Fetch individual logged-in employee's requests for the view data-table
        $leaveRequests = $employee 
        ? LeaveRequest::with('details')
            ->where('employee_id', $employee->id)
            ->where('created_at', '>=', now()->subDays(30)) // Hides items older than 30 days
            ->orderBy('created_at', 'desc')
            ->get() 
        : collect();

        $calendarEvents = [];

        // Generate Regular Holidays to ensure the calendar works perfectly
        $this->ensureRegularHolidaysExist(date('Y'));
        $this->ensureRegularHolidaysExist(date('Y') + 1);
        $this->ensureRegularHolidaysExist(2026);

        // 1. ALL Holidays (RESTORED: Pulling from local holiday table)
        $holidays = CustomHoliday::all();
        foreach ($holidays as $holiday) {
            $isRegular = ($holiday->type === 'regular'); 
            $displayTitle = $holiday->name . ($holiday->is_half_day ? ' (Half-Day)' : '');

            $calendarEvents[] = [
                'id' => 'custom_'.$holiday->id,
                'title' => $displayTitle,
                'start' => $holiday->date,
                'backgroundColor' => $isRegular ? '#3b82f6' : '#f97316',
                'borderColor' => $isRegular ? '#2563eb' : '#ea580c',
                'textColor' => '#ffffff',
                'allDay' => true,
                'extendedProps' => ['type' => 'custom_holiday', 'holiday_id' => $holiday->id]
            ];
        }

        // 2. Corporate Leaves (FIXED: Filters by Division if the user is an employee)
        $user = Auth::user();
        
        // Start building the filtered base query builder
        $leaveQuery = LeaveRequest::with(['employee', 'details'])
            ->whereIn('status', ['pending', 'approved', 'PENDING', 'APPROVED']);

        // If the user is NOT an admin, lock them down to their division
        if ($user->role !== 'admin') { 
            // Defensive check to ensure the user has an employee record assigned
            $userDivisionId = $user->employee ? $user->employee->division_id : null;

            if ($userDivisionId) {
                // Only fetch leaves where the related employee belongs to the same division
                $leaveQuery->whereHas('employee', function ($query) use ($userDivisionId) {
                    $query->where('division_id', $userDivisionId);
                });
            }
        }
        // Get the logged-in user's employee ID before the loop starts
        $myEmployeeId = $user->employee ? $user->employee->id : null;

        // FIXED: Call ->get() on your configured builder instead of re-instantiating a clean one
        $allLeaves = $leaveQuery->get();

       foreach ($allLeaves as $leave) {
            $isPending = in_array($leave->status, ['pending', 'PENDING']);
            $isMyLeave = ($leave->employee_id === $myEmployeeId);

            // Color Logic: Separate "Mine" vs "Others"
            if ($isMyLeave) {
                // MY LEAVES: Vibrant colors so they stand out
                $bgColor = $isPending ? '#eab308' : '#22c55e'; // Tailwind Yellow-500 / Green-500
                $bdColor = $isPending ? '#ca8a04' : '#16a34a'; // Tailwind Yellow-600 / Green-600
            } else {
                // OTHERS' LEAVES: Muted Slate/Gray colors so they fade into the background
                $bgColor = $isPending ? '#94a3b8' : '#64748b'; // Tailwind Slate-400 / Slate-500
                $bdColor = $isPending ? '#64748b' : '#475569'; // Tailwind Slate-500 / Slate-600
            }

        $employeeName = $leave->employee ? $leave->employee->first_name : 'Employee';

            // Loop over each explicit date row inside your leave_request_details table
            foreach ($leave->details as $detail) {
                $calendarEvents[] = [
                    'title' => $employeeName . ' (' . $leave->leave_type . ')',
                    'start' => $detail->leave_date->format('Y-m-d'), 
                    'backgroundColor' => $bgColor,
                    'borderColor' => $bdColor,
                    'textColor' => '#ffffff',
                    'allDay' => true,
                    'extendedProps' => [
                        'type' => $isPending ? 'pending_leave' : 'approved_leave',
                        'leave_id' => $leave->id,
                        'detail_id' => $detail->id 
                    ]
                ];
            }
        }

        return view('leave_requests.index', compact('employee', 'leaveRequests', 'calendarEvents'));
    }

    public function create()
    {
        $employee = Auth::user()->employee;
        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'You do not have an employee profile linked to your account yet.');
        }
        
        // 1. Get the current user's booked dates
        $myBookedDates = $this->leaveService->getBookedDates(
            LeaveRequest::where('employee_id', $employee->id)
                ->whereIn('status', ['pending', 'approved', 'PENDING', 'APPROVED'])
        );

        // 2. NEW: Get approved dates ONLY for other employees in the SAME division
        $divisionApprovedDates = $this->leaveService->getBookedDates(
            LeaveRequest::whereIn('status', ['approved', 'APPROVED'])
                ->whereHas('employee', function($q) use ($employee) {
                    $q->where('division_id', $employee->division_id)
                    ->where('id', '!=', $employee->id);
                })
        );

        // 3. NEW: Get pending dates ONLY for other employees in the SAME division
        $divisionPendingDates = $this->leaveService->getBookedDates(
            LeaveRequest::whereIn('status', ['pending', 'PENDING'])
                ->whereHas('employee', function($q) use ($employee) {
                    $q->where('division_id', $employee->division_id)
                    ->where('id', '!=', $employee->id);
                })
        );
        
        // 4. Get holidays
        $holidayDates = array_column($this->leaveService->getPhilippineHolidays(date('Y')), 'date'); 
        
        // 5. Merge all dates that should be completely disabled on the calendar (Added divisionPendingDates here!)
        $disabledDates = array_values(array_unique(array_merge($myBookedDates, $divisionApprovedDates, $divisionPendingDates, $holidayDates)));

        // Pass the new variables to the view
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

        // Balance Check
        $balanceField = $this->leaveService->getBalanceField($validated['leave_type']);
        if ($balanceField && $employee->$balanceField < $validated['working_days_applied']) {
            return back()->withInput()->withErrors(['working_days_applied' => "Insufficient balance. You only have {$employee->$balanceField} days left."]);
        }

        // Holiday Processing & Day Validation
        $holidayData = array_merge(
            $this->leaveService->getPhilippineHolidays(Carbon::parse($rawDates[0])->year),
            $this->leaveService->getPhilippineHolidays(Carbon::parse(end($rawDates))->year)
        );
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
        
        $query = LeaveRequest::with('employee.department', 'employee.division', 'details')
            ->orderBy('created_at', 'desc');

        // --- 1. ROLE-BASED ACCESS & SYSTEM-ADMIN EXCLUSION ---
        if ($admin->is_admin === \App\Models\User::ROLE_SUPER_ADMIN) {
            // Super Admin: Hide SYSTEM-ADMIN department leaves
            $query->whereHas('employee.department', function($q) {
                $q->where('code', '!=', 'SYSTEM-ADMIN');
            });
            
            // FIX: Get valid department IDs first to avoid the missing relationship error on Division
            $validDeptIds = \App\Models\Department::where('code', '!=', 'SYSTEM-ADMIN')->pluck('id');
            $divisions = \App\Models\Division::whereIn('department_id', $validDeptIds)->get();
            
        } else {
            // Department Admin: Get only their department
            $deptId = $admin->employee ? $admin->employee->department_id : null;
            
            $query->whereHas('employee', function($q) use ($deptId) {
                $q->where('department_id', $deptId);
            });
            
            // Get only divisions for their specific department
            $divisions = \App\Models\Division::where('department_id', $deptId)->get();
        }

        // --- 2. APPLY USER FILTERS ---
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('leave_type', 'like', "%{$search}%")
                ->orWhereHas('employee', function ($empQ) use ($search) {
                    $empQ->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('division')) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('division_id', $request->input('division'));
            });
        }

        // --- 3. CALENDAR EVENTS ---
        $calendarEvents = [];
        $this->ensureRegularHolidaysExist(date('Y'));
        $this->ensureRegularHolidaysExist(date('Y') + 1);
        $this->ensureRegularHolidaysExist(2026);

        $holidays = \App\Models\CustomHoliday::all(); 
        foreach ($holidays as $holiday) {
            $isRegular = ($holiday->type === 'regular'); 
            $calendarEvents[] = [
                'id' => 'custom_'.$holiday->id,
                'title' => $holiday->name . ($holiday->is_half_day ? ' (Half-Day)' : ''),
                'start' => $holiday->date,
                'backgroundColor' => $isRegular ? '#3b82f6' : '#f97316',
                'borderColor' => $isRegular ? '#2563eb' : '#ea580c',
                'textColor' => '#ffffff',
                'allDay' => true,
                'extendedProps' => ['type' => 'custom_holiday', 'holiday_id' => $holiday->id]
            ];
        }

        // Get the logged-in admin's employee ID before the loop starts
        $myEmployeeId = $admin->employee ? $admin->employee->id : null;

        $allLeaves = $query->get(); 
        
        foreach ($allLeaves as $leave) {
            // Standardize status to lowercase just to be safe
            $status = strtolower($leave->status);

            // Admin Color Logic: Strictly Status-Based
            if ($status === 'pending') {
                $bgColor = '#fff2cb'; // Tailwind Yellow-500
                $bdColor = '#ca8a04'; // Tailwind Yellow-600
            } elseif ($status === 'approved') {
                $bgColor = '#c1f7d5'; // Tailwind Green-500
                $bdColor = '#16a34a'; // Tailwind Green-600
            } else {
                // Fallback for Disapproved (Red)
                $bgColor = '#ef4444'; // Tailwind Red-500
                $bdColor = '#dc2626'; // Tailwind Red-600
            }

            foreach ($leave->details as $detail) {
                $calendarEvents[] = [
                    'title' => ($leave->employee->first_name ?? 'N/A') . ' - ' . $leave->leave_type,
                    'start' => $detail->leave_date->format('Y-m-d'),
                    'backgroundColor' => $bgColor,
                    'borderColor' => $bdColor,
                    'textColor' => '#1a8026',
                    'allDay' => true, 
                    'extendedProps' => [ 
                        'type' => 'leave_request', 
                        'leave_id' => $leave->id,
                        'status' => $leave->status 
                    ]
                ];
            }
        
        }

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

        // Security Check: Make sure standard employees only see their own!
        if (!$user->is_admin && $user->employee && $leaveRequest->employee_id !== $user->employee->id) {
            abort(403, 'Unauthorized action. You can only view your own leave requests.');
        }

        // Reuses your existing review blade
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
        $status = $request->status;

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
        $user = auth()->user();
        $employee = $user->employee; 

        // If no employee profile is linked, redirect back with an error
        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'You must have a linked employee profile to view history.');
        }

        // Start the query isolated to ONLY this employee
        $query = LeaveRequest::where('employee_id', $employee->id);

        // 1. Keyword Search (matches Type or Details)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('leave_type', 'like', "%{$search}%")
                  ->orWhere('leave_type_others', 'like', "%{$search}%")
                  ->orWhere('leave_detail_category', 'like', "%{$search}%")
                  ->orWhere('leave_detail_specifics', 'like', "%{$search}%");
            });
        }

        // Filter by Month and Year (replaces Leave Type dropdown)
        if ($request->filled('timeframe')) {
            switch ($request->input('timeframe')) {
                case 'this_month':
                    $query->whereMonth('date_of_filing', now()->month)
                        ->whereYear('date_of_filing', now()->year);
                    break;
                case 'last_3_months':
                    $query->where('date_of_filing', '>=', now()->subMonths(3));
                    break;
                case 'this_year':
                    $query->whereYear('date_of_filing', now()->year);
                    break;
            }
        }

        // 3. Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // 3. Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // 4. Handle Sorting Dynamically
        $query->orderBy('created_at', 'desc');

        // Use pagination instead of get() so the page doesn't crash if they have 500 records
        $leaveRequests = $query->paginate(15)->withQueryString();

        return view('leave_requests.history', compact('leaveRequests'));
    }

    /**
     * Generates standard PH holidays into the database if they don't exist yet.
     */
    private function ensureRegularHolidaysExist($year)
    {
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

        foreach ($regularHolidays as $holiday) {
            $exists = CustomHoliday::where('name', $holiday['name'])
                                   ->whereYear('date', $year)
                                   ->exists();

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