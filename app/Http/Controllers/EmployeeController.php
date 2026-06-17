<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\Department;
use App\Models\Division;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function index()
    {
        $loggedInAdmin = auth()->user();

        // Fetch employees based on role excluding super admin
        if ($loggedInAdmin->is_admin === User::ROLE_SUPER_ADMIN) {
            $employeesQuery = Employee::with(['division.department', 'user', 'leaveBalances'])
                ->where('employee_id_number', '!=', '0000000')
                ->get();
        } else {
            // Both Admin Officers (1) and Dept Heads (3) fall here and safely get only their department's team
            $departmentId = $loggedInAdmin->employee?->division?->department_id;

            $employeesQuery = Employee::with(['division.department', 'user', 'leaveBalances'])
                ->whereHas('division', function ($query) use ($departmentId) {
                    $query->where('department_id', $departmentId);
                })
                ->where('employee_id_number', '!=', '0000000')
                ->get();
        }

        // Group the table by the department name
        $groupedEmployees = $employeesQuery->groupBy(function($employee) {
            return $employee->division && $employee->division->department 
                ? $employee->division->department->department_name 
                : 'Unassigned Department';
        });

        // Fetch active leave types for dynamic table headers
        $leaveTypes = \App\Models\LeaveType::all();

        // Pass BOTH groupedEmployees and leaveTypes to the view
        return view('employees.index', compact('groupedEmployees', 'leaveTypes'));
    }

    public function create()
    {
        $departments = Department::where('code', '!=', 'SYSTEM-ADMIN')->get();
        $divisions = Division::all();
        
        // 🎯 FIX: Only send the 4 core leaves to the frontend creation form
        $leaveTypes = \App\Models\LeaveType::whereIn('code', ['VL', 'SL', 'FL', 'SPL'])->get();
        
        return view('employees.create', compact('departments', 'divisions', 'leaveTypes'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'employee_id_number' => 'required|string|unique:employees,employee_id_number|max:10',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:1',
            'position' => 'required|string|max:255',
            'position_code' => 'required|string|max:20',
            
            'balances' => 'required|array',
            'balances.*' => 'numeric|min:0',
        ]);

        DB::transaction(function () use ($validatedData) {
            $employee = Employee::create([
                'division_id' => $validatedData['division_id'],
                'employee_id_number' => $validatedData['employee_id_number'],
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'middle_initial' => $validatedData['middle_initial'],
                'position' => $validatedData['position'],
                'position_code' => $validatedData['position_code'],
            ]);

            // 🎯 FIX: Query ALL 13 system leaves from the database
            $allLeaveTypes = \App\Models\LeaveType::all();

            foreach ($allLeaveTypes as $type) {
                // If it was submitted via the form, use that value. Otherwise, default to 0.00
                $balanceAmount = $validatedData['balances'][$type->id] ?? 0.00;

                $employee->leaveBalances()->create([
                    'leave_type_id' => $type->id,
                    'balance' => $balanceAmount,
                    'year' => now()->year,
                ]);
            }
        });

        return redirect()->route('employees.index')->with('success', 'Employee created successfully!');
    }

    public function show(string $id)
    {
        // 1. Fetch the employee with relationships preloaded (Your existing code - perfect!)
        $employee = Employee::with(['division.department', 'user', 'leaveBalances.leaveType'])->findOrFail($id);
        
        // 2. ADD THIS: Fetch all leave types so the Blade file can build the list dynamically
        $leaveTypes = \App\Models\LeaveType::all();

        // 3. UPDATE THIS: Pass both 'employee' and 'leaveTypes' to the view
        return view('employees.show', compact('employee', 'leaveTypes'));
    }

    public function edit(string $id)
    {
        $employee = Employee::with('leaveBalances')->findOrFail($id);
        $departments = Department::where('code', '!=', 'SYSTEM-ADMIN')->get();
        $divisions = Division::all();
        
        // 🎯 FIX: Only display the 4 core leaves on the editing screen
        $leaveTypes = \App\Models\LeaveType::whereIn('code', ['VL', 'SL', 'FL', 'SPL'])->get();

        return view('employees.edit', compact('employee', 'departments', 'divisions', 'leaveTypes'));
    }

    public function update(Request $request, string $id)
    {
        $employee = Employee::findOrFail($id);

        $validatedData = $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:1',
            'position' => 'required|string|max:255',
            'position_code' => 'required|string|max:20',

            'balances' => 'required|array',
            'balances.*' => 'numeric|min:0',
        ]);

        DB::transaction(function () use ($employee, $validatedData) {
            $employee->update([
                'division_id' => $validatedData['division_id'],
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'middle_initial' => $validatedData['middle_initial'],
                'position' => $validatedData['position'],
                'position_code' => $validatedData['position_code'],
            ]);

            // 🎯 FIX: Safely update only the 4 core leaves submitted by the form
            foreach ($validatedData['balances'] as $leaveTypeId => $balanceAmount) {
                $employee->leaveBalances()->updateOrCreate(
                    ['leave_type_id' => $leaveTypeId],
                    ['balance' => $balanceAmount, 'year' => now()->year]
                );
            }
        });

        return redirect()->route('employees.show', $employee->id)->with('success', 'Employee updated successfully!');
    }

    public function destroy(string $id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully!');
    }

    public function changeRole(Request $request, string $id)
    {
        $currentUser = auth()->user();

        if (!in_array($currentUser->is_admin, [User::ROLE_SUPER_ADMIN, User::ROLE_DEPT_HEAD])) {
            abort(403, 'Unauthorized action.');
        }

        $employee = Employee::with(['user', 'division'])->findOrFail($id);
        $employeeDeptId = $employee->division?->department_id;

        if (!$employee->user) {
            return redirect()->back()->withErrors(['error' => 'Cannot change role: This employee does not have a registered user account yet.']);
        }

        if ($currentUser->is_admin === User::ROLE_DEPT_HEAD) {
            $myDepartmentId = $currentUser->employee?->division?->department_id;
            if ($employeeDeptId !== $myDepartmentId) {
                abort(403, 'Unauthorized action. You can only alter roles for members within your own department.');
            }
        }

        $allowedRoles = $currentUser->is_admin === User::ROLE_SUPER_ADMIN ? '0,1,2,3' : '0,1';

        $request->validate([
            'role' => 'required|integer|in:' . $allowedRoles,
        ]);

        if ($request->role == User::ROLE_ADMIN_OFFICER) {
            $existingAdmin = Employee::whereHas('division', function ($query) use ($employeeDeptId) {
                    $query->where('department_id', $employeeDeptId);
                })
                ->where('id', '!=', $employee->id) 
                ->whereHas('user', function ($query) {
                    $query->where('is_admin', User::ROLE_ADMIN_OFFICER);
                })
                ->first();

            if ($existingAdmin) {
                $errorMsg = 'Cannot assign role: ' . $existingAdmin->first_name . ' ' . $existingAdmin->last_name . ' is already the Admin Officer for this department. Please demote them to an Employee first.';
                return redirect()->back()->withErrors(['error' => $errorMsg]);
            }
        }

        if ($request->role == User::ROLE_DEPT_HEAD) {
            $existingHead = Employee::whereHas('division', function ($query) use ($employeeDeptId) {
                    $query->where('department_id', $employeeDeptId);
                })
                ->where('id', '!=', $employee->id)
                ->whereHas('user', function ($query) {
                    $query->where('is_admin', User::ROLE_DEPT_HEAD);
                })
                ->first();

            if ($existingHead) {
                $errorMsg = 'Cannot assign role: ' . $existingHead->first_name . ' ' . $existingHead->last_name . ' is already the Department Head for this department.';
                return redirect()->back()->withErrors(['error' => $errorMsg]);
            }
        }

        $employee->user->update([
            'is_admin' => $request->role
        ]);

        return redirect()->back()->with('success', 'User role updated successfully!');
    }

   /**
     * 🌟 INDIVIDUAL ALLOCATION: Manually add +1.25 to a single employee
     */
    public function allocateMonthlyCredits(Request $request, Employee $employee)
    {
        // 1. Strict Security Check: Only allow on the 1st day of the month
        // if (now()->day !== 1) {
        //     return redirect()->back()->withErrors(['error' => 'Monthly credits can only be posted on the 1st day of the month.']);
        // }

        $vlType = \App\Models\LeaveType::where('code', 'VL')->first();
        
        // 🌟 DUPLICATE CHECK: Look for existing accrual this month
        $alreadyAllocated = \App\Models\LeaveLedger::where('employee_id', $employee->id)
            ->where('leave_type_id', $vlType->id)
            ->where('reason_code', 'MONTHLY_ACCRUAL')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->exists();

        if ($alreadyAllocated) {
            return redirect()->back()->withErrors(['error' => "{$employee->first_name} has already received their monthly accrual for this month."]);
        }

        DB::beginTransaction();
        try {
            $slType = \App\Models\LeaveType::where('code', 'SL')->first();
            $currentYear = now()->year;

            // VL Allocation
            $vlBalance = EmployeeLeaveBalance::firstOrCreate(
                ['employee_id' => $employee->id, 'leave_type_id' => $vlType->id, 'year' => $currentYear],
                ['balance' => 0]
            );
            $vlBalance->balance += 1.25;
            $vlBalance->save();

            \App\Models\LeaveLedger::create([
                'employee_id' => $employee->id,
                'leave_type_id' => $vlType->id,
                'type' => 'accrual', // 🌟 CHANGED FROM 'credit'
                'amount' => 1.25,
                'running_balance' => $vlBalance->balance,
                'created_by' => auth()->id(),
                'reason_code' => 'MONTHLY_ACCRUAL',
                'remarks' => 'Manual Monthly Accrual Allocation (Standard)',
            ]);

            // SL Allocation
            $slBalance = EmployeeLeaveBalance::firstOrCreate(
                ['employee_id' => $employee->id, 'leave_type_id' => $slType->id, 'year' => $currentYear],
                ['balance' => 0]
            );
            $slBalance->balance += 1.25;
            $slBalance->save();

            \App\Models\LeaveLedger::create([
                'employee_id' => $employee->id,
                'leave_type_id' => $slType->id,
                'type' => 'accrual', // 🌟 CHANGED FROM 'credit'
                'amount' => 1.25,
                'running_balance' => $slBalance->balance,
                'created_by' => auth()->id(),
                'reason_code' => 'MONTHLY_ACCRUAL',
                'remarks' => 'Manual Monthly Accrual Allocation (Standard)',
            ]);

            DB::commit();
            return redirect()->back()->with('success', '1.25 Standard Monthly Credits successfully posted to VL and SL.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Failed to allocate credits: ' . $e->getMessage()]);
        }
    }

    /**
     * 🌟 MASS ALLOCATION: Add +1.25 to employees (Scoped by Admin Role)
     */
    public function massAllocateMonthlyCredits(Request $request)
    {
        // if (now()->day !== 1) {
        //     return redirect()->back()->withErrors(['error' => 'Mass allocation can only be executed on the 1st day of the month.']);
        // }

        DB::beginTransaction();
        try {
            $vlType = \App\Models\LeaveType::where('code', 'VL')->first();
            $slType = \App\Models\LeaveType::where('code', 'SL')->first();
            $currentMonth = now()->month;
            $currentYear = now()->year;
            $loggedInAdmin = auth()->user();

            // 🌟 SECURITY SCOPE: Super Admins get everyone. Dept Heads/Officers only get their department.
            if ($loggedInAdmin->is_admin === \App\Models\User::ROLE_SUPER_ADMIN) {
                $employees = Employee::where('employee_id_number', '!=', '0000000')->get();
            } else {
                $departmentId = $loggedInAdmin->employee?->division?->department_id;
                $employees = Employee::whereHas('division', function ($query) use ($departmentId) {
                    $query->where('department_id', $departmentId);
                })->where('employee_id_number', '!=', '0000000')->get();
            }

            $count = 0;

            foreach ($employees as $employee) {
                // 🌟 SKIP CHECK: See if they were already processed
                $alreadyAllocated = \App\Models\LeaveLedger::where('employee_id', $employee->id)
                    ->where('leave_type_id', $vlType->id)
                    ->where('reason_code', 'MONTHLY_ACCRUAL')
                    ->whereMonth('created_at', $currentMonth)
                    ->whereYear('created_at', $currentYear)
                    ->exists();

                if ($alreadyAllocated) {
                    continue; // Skip to next employee
                }

                // VL
                $vlBalance = EmployeeLeaveBalance::firstOrCreate(
                    ['employee_id' => $employee->id, 'leave_type_id' => $vlType->id, 'year' => $currentYear],
                    ['balance' => 0]
                );
                $vlBalance->balance += 1.25;
                $vlBalance->save();

                \App\Models\LeaveLedger::create([
                    'employee_id' => $employee->id, 'leave_type_id' => $vlType->id, 
                    'type' => 'accrual', // 🌟 CHANGED FROM 'credit'
                    'amount' => 1.25, 'running_balance' => $vlBalance->balance, 'created_by' => $loggedInAdmin->id,
                    'reason_code' => 'MONTHLY_ACCRUAL', 'remarks' => 'Mass Monthly Accrual Allocation',
                ]);

                // SL
                $slBalance = EmployeeLeaveBalance::firstOrCreate(
                    ['employee_id' => $employee->id, 'leave_type_id' => $slType->id, 'year' => $currentYear],
                    ['balance' => 0]
                );
                $slBalance->balance += 1.25;
                $slBalance->save();

                \App\Models\LeaveLedger::create([
                    'employee_id' => $employee->id, 'leave_type_id' => $slType->id, 
                    'type' => 'accrual',  // 🌟 CHANGED FROM 'credit'
                    'amount' => 1.25, 'running_balance' => $slBalance->balance, 'created_by' => $loggedInAdmin->id,
                    'reason_code' => 'MONTHLY_ACCRUAL', 'remarks' => 'Mass Monthly Accrual Allocation',
                ]);
                
                $count++;
            }

            DB::commit();

            // Custom success message depending on if everyone was skipped
            if ($count === 0) {
                return redirect()->back()->with('success', "No credits were posted. All selected employees have already received their monthly allocation.");
            }

            return redirect()->back()->with('success', "Mass allocation complete. {$count} employees were credited (others were skipped).");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Mass allocation failed: ' . $e->getMessage()]);
        }
    }
}