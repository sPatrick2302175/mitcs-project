<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\Employee;
use App\Models\User;
use App\Models\Department;
use App\Models\Division;
use App\Http\Requests\StoreLeaveRequest;
use App\Http\Requests\ProcessLeaveActionRequest;
use App\Services\LeaveManagementService;
use App\Services\LeaveFormService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\CalendarService;

class LeaveRequestController extends Controller
{
    protected $leaveService;
    protected $pdfService;
    protected $calendarService;

    public function __construct(LeaveManagementService $leaveService, LeaveFormService $pdfService, CalendarService $calendarService)
    {
        $this->leaveService = $leaveService;
        $this->pdfService = $pdfService;
        $this->calendarService = $calendarService;
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

        $leaveQuery = LeaveRequest::with(['employee', 'details'])
            ->whereIn('status', ['pending', 'approved']);

        if ($user->role !== 'admin' && $employee?->division_id) {
            $leaveQuery->whereHas('employee', function ($query) use ($employee) {
                $query->where('division_id', $employee->division_id);
            });
        }

        $allLeaves = $leaveQuery->get();

        $calendarEvents = array_merge(
            $this->calendarService->getHolidayEvents(),
            $this->calendarService->getLeaveEvents($allLeaves, $employee?->id, false)
        );

        return view('leave_requests.index', compact('employee', 'leaveRequests', 'calendarEvents'));
    }

    public function create()
    {
        $employee = Auth::user()->employee;
        
        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'You do not have an employee profile linked to your account yet.');
        }
        
        // CLEANED UP: All date computations and array merging happen in the service layer
        $calendarData = $this->leaveService->getLeaveCalendarData($employee);

        return view('leave_requests.create', $calendarData);
    }

    public function store(StoreLeaveRequest $request)
    {
        $employee = Auth::user()->employee;
        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'Employee profile missing.');
        }

        try {
            // Hand off all processing to the service layer
            $this->leaveService->createLeaveApplication($employee, $request->validated());

            return redirect()->route('leave-requests.index')
                ->with('success', 'Leave application submitted successfully!');
                
        } catch (\InvalidArgumentException $e) {
            // Automatically returns the exact rule failure message back to your frontend validation error block
            return back()->withInput()->withErrors(['selected_dates' => $e->getMessage()]);
        }
    }

    public function adminIndex(Request $request)
    {
        $admin = auth()->user();
        $query = LeaveRequest::with('employee.department', 'employee.division', 'details')->latest();

        // (Role and division logic stays here, as it dictates the base query rules)
        if ($admin->is_admin === User::ROLE_SUPER_ADMIN) {
            $query->whereHas('employee.department', fn($q) => $q->where('code', '!=', 'SYSTEM-ADMIN'));
            $validDeptIds = Department::where('code', '!=', 'SYSTEM-ADMIN')->pluck('id');
            $divisions = Division::whereIn('department_id', $validDeptIds)->get();
        } else {
            $deptId = $admin->employee?->department_id;
            $query->whereHas('employee', fn($q) => $q->where('department_id', $deptId));
            $divisions = Division::where('department_id', $deptId)->get();
        }

        // CLEANED UP: Replaced the massive closure with our single search scope
        $query->search($request->search)
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->division, fn($q, $division) => $q->whereHas('employee', fn($empQ) => $empQ->where('division_id', $division)));

        $allLeaves = $query->get();
        
        $calendarEvents = array_merge(
            $this->calendarService->getHolidayEvents(),
            $this->calendarService->getLeaveEvents($allLeaves, null, true)
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

        // CLEANED UP: Utilizing our new custom scopes
        $leaveRequests = LeaveRequest::where('employee_id', $employee->id)
            ->search($request->search)
            ->withinTimeframe($request->timeframe)
            ->when($request->status, fn($q, $status) => $q->where('status', strtolower($status)))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('leave_requests.history', compact('leaveRequests'));
    }
    
}