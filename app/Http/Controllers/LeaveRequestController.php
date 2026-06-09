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
        $leaveRequests = $employee 
        ? LeaveRequest::where('employee_id', $employee->id)
            ->where('created_at', '>=', now()->subDays(30)) //Hides items older than 30 days
            ->orderBy('created_at', 'desc')
            ->get() 
        : collect();

        $apiHolidays = $this->leaveService->getPhilippineHolidays(date('Y'));
        $calendarEvents = array_map(fn($holiday) => [
            'title' => $holiday['name'],
            'start' => $holiday['date'],
            'backgroundColor' => '#3b82f6', 
            'borderColor' => '#2563eb',
            'textColor' => '#ffffff',
            'allDay' => true,
            'display' => 'block'
        ], $apiHolidays);

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
        
        // 1. Initialize query with eager loading for optimization
        $query = LeaveRequest::with('employee.department')->orderBy('created_at', 'desc');

        // 2. KEEP EXISTING PROTECTION: Enforce department boundaries for standard admins
        if ($admin->is_admin !== User::ROLE_SUPER_ADMIN) {
            $deptId = $admin->employee ? $admin->employee->department_id : null;
            $query->whereHas('employee', fn($q) => $q->where('department_id', $deptId));
        }

        // 3. NEW: Multi-Parameter Live Search Handler (Name, ID, or Leave Type)
        if ($request->filled('search')) {
            $search = $request->input('search');

            // We nest this inside a where closure to prevent breaking the department scope above
            $query->where(function ($q) use ($search) {
                $q->where('leave_type', 'like', "%{$search}%")
                ->orWhere('leave_type_others', 'like', "%{$search}%")
                ->orWhereHas('employee', function ($empQ) use ($search) {
                    $empQ->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('employee_id_number', 'like', "%{$search}%")
                        // Optional premium touch: handles searching full names "First Last" combined
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                });
            });
        }

        // 4. NEW: Status Dropdown Filter Handler
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // 5. Render view with dynamic matches array
        return view('leave_requests.admin_index', [
            'leaveRequests' => $query->get()
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
}