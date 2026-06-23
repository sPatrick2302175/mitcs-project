<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\Department;
use App\Models\Division;
use App\Models\User;
use App\Models\LeaveLedger;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $loggedInAdmin = auth()->user();
        $search = $request->input('search');

        // Start the base query
        $query = Employee::with(['division.department', 'user', 'leaveBalances'])
            ->where('employee_id_number', '!=', '0000000');

        // Role-based filtering
        if ($loggedInAdmin->is_admin !== User::ROLE_SUPER_ADMIN) {
            $departmentId = $loggedInAdmin->employee?->division?->department_id;
            $query->whereHas('division', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        // Search filtering (Name or Employee ID)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('employee_id_number', 'like', "%{$search}%");
            });
        }

        // Fetch the results
        $employeesQuery = $query->get();

        // Group the table by the department name
        $groupedEmployees = $employeesQuery->groupBy(function($employee) {
            return $employee->division && $employee->division->department 
                ? $employee->division->department->department_name 
                : 'Unassigned Department';
        });

        $leaveTypes = LeaveType::all();

       

        return view('employees.index', compact('groupedEmployees', 'leaveTypes'));
    }

    public function create()
    {
        $departments = Department::where('code', '!=', 'SYSTEM-ADMIN')->get();
        $divisions = Division::all();
        
        // Only send the 4 core leaves to the frontend creation form
        $leaveTypes = LeaveType::all();
        
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

            // Query ALL 13 system leaves from the database
            $allLeaveTypes = LeaveType::all();

            foreach ($allLeaveTypes as $type) {
                // If it was submitted via the form, use that value. Otherwise, default to 0.00
                $balanceAmount = $validatedData['balances'][$type->id] ?? 0.0000;

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
        // Fetch the employee with relationships preloaded
        $employee = Employee::with(['division.department', 'user', 'leaveBalances.leaveType'])->findOrFail($id);
        
        // Fetch all leave types so the Blade file can build the list dynamically
        $leaveTypes = LeaveType::all();

        // Pass both 'employee' and 'leaveTypes' to the view
        return view('employees.show', compact('employee', 'leaveTypes'));
    }

    public function edit(string $id)
    {
        $employee = Employee::with('leaveBalances')->findOrFail($id);
        $departments = Department::where('code', '!=', 'SYSTEM-ADMIN')->get();
        $divisions = Division::all();
        
        // Only display the 4 core leaves on the editing screen
        $leaveTypes = LeaveType::all();

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

            // Safely update only the 4 core leaves submitted by the form AND record ledger adjustments
            foreach ($validatedData['balances'] as $leaveTypeId => $balanceAmount) {
                $currentYear = now()->year;
                
                // 1. Retrieve the existing balance record (if it exists)
                $leaveBalanceRecord = $employee->leaveBalances()
                    ->where('leave_type_id', $leaveTypeId)
                    ->where('year', $currentYear)
                    ->first();

                $oldBalance = $leaveBalanceRecord ? (float) $leaveBalanceRecord->balance : 0.0000;
                $newBalance = (float) $balanceAmount;

                // 2. Check if the admin actually changed the balance amount
                // (Rounding to 2 decimals prevents floating point errors)
                if (round($oldBalance, 4) !== round($newBalance, 4)) {
                    
                    // Calculate the difference to see if it was added to or subtracted from
                    $difference = $newBalance - $oldBalance;
                    $adjustmentType = $difference > 0 ? 'accrual' : 'deduction';
                    $absoluteAmount = abs($difference);

                    // 3. Update or create the balance record
                    if ($leaveBalanceRecord) {
                        $leaveBalanceRecord->update(['balance' => $newBalance]);
                    } else {
                        $employee->leaveBalances()->create([
                            'leave_type_id' => $leaveTypeId,
                            'balance' => $newBalance,
                            'year' => $currentYear
                        ]);
                    }

                    // 4. Insert the audit trail into the Leave Ledger
                    LeaveLedger::create([
                        'employee_id' => $employee->id,
                        'leave_type_id' => $leaveTypeId,
                        'type' => $adjustmentType,
                        'amount' => $absoluteAmount,
                        'running_balance' => $newBalance,
                        'created_by' => auth()->id(),
                        'reason_code' => 'MANUAL_ADJUSTMENT',
                        'remarks' => "Manual balance adjustment by Admin (Old: {$oldBalance}, New: {$newBalance})",
                    ]);
                }
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
     * INDIVIDUAL ALLOCATION: Manually add dynamic amount to a single employee
     */
    public function allocateMonthlyCredits(Request $request, Employee $employee)
    {
        // 1. Strict Validation: Ensure they passed a valid positive number
        $request->validate([
            'allocation_amount' => 'required|numeric|min:0.01'
        ]);

        // 2. Fetch the inputted amount
        $allocationAmount = (float) $request->input('allocation_amount');

        DB::beginTransaction();
        try {
            $vlType = LeaveType::where('code', 'VL')->first();
            $slType = LeaveType::where('code', 'SL')->first();
            $currentYear = now()->year;

            // VL Allocation
            $vlBalance = EmployeeLeaveBalance::firstOrCreate(
                ['employee_id' => $employee->id, 'leave_type_id' => $vlType->id, 'year' => $currentYear],
                ['balance' => 0]
            );
            $vlBalance->balance += $allocationAmount;
            $vlBalance->save();

            LeaveLedger::create([
                'employee_id' => $employee->id,
                'leave_type_id' => $vlType->id,
                'type' => 'accrual', 
                'amount' => $allocationAmount,
                'running_balance' => $vlBalance->balance,
                'created_by' => auth()->id(),
                'reason_code' => 'MONTHLY_ACCRUAL',
                'remarks' => "Manual Accrual Allocation (+{$allocationAmount})",
            ]);

            // SL Allocation
            $slBalance = EmployeeLeaveBalance::firstOrCreate(
                ['employee_id' => $employee->id, 'leave_type_id' => $slType->id, 'year' => $currentYear],
                ['balance' => 0]
            );
            $slBalance->balance += $allocationAmount;
            $slBalance->save();

            LeaveLedger::create([
                'employee_id' => $employee->id,
                'leave_type_id' => $slType->id,
                'type' => 'accrual', 
                'amount' => $allocationAmount,
                'running_balance' => $slBalance->balance,
                'created_by' => auth()->id(),
                'reason_code' => 'MONTHLY_ACCRUAL',
                'remarks' => "Manual Accrual Allocation (+{$allocationAmount})",
            ]);

            DB::commit();
            return redirect()->back()->with('success', "+{$allocationAmount} Credits successfully posted to VL and SL.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Failed to allocate credits: ' . $e->getMessage()]);
        }
    }

    /**
     * MASS ALLOCATION: Add dynamic amount to employees (Scoped by Admin Role)
     */
    public function massAllocateMonthlyCredits(Request $request)
    {
        // 1. Strict Validation: Ensure they passed a valid positive number
        $request->validate([
            'allocation_amount' => 'required|numeric|min:0.01'
        ]);

        // 2. Fetch the inputted amount
        $allocationAmount = (float) $request->input('allocation_amount');

        DB::beginTransaction();
        try {
            $vlType = LeaveType::where('code', 'VL')->first();
            $slType = LeaveType::where('code', 'SL')->first();
            $currentYear = now()->year;
            $loggedInAdmin = auth()->user();

            // Super Admins get everyone. Dept Heads/Officers only get their department.
            if ($loggedInAdmin->is_admin === User::ROLE_SUPER_ADMIN) {
                $employees = Employee::where('employee_id_number', '!=', '0000000')->get();
            } else {
                $departmentId = $loggedInAdmin->employee?->division?->department_id;
                $employees = Employee::whereHas('division', function ($query) use ($departmentId) {
                    $query->where('department_id', $departmentId);
                })->where('employee_id_number', '!=', '0000000')->get();
            }

            $count = 0;

            foreach ($employees as $employee) {
                // VL
                $vlBalance = EmployeeLeaveBalance::firstOrCreate(
                    ['employee_id' => $employee->id, 'leave_type_id' => $vlType->id, 'year' => $currentYear],
                    ['balance' => 0]
                );
                $vlBalance->balance += $allocationAmount;
                $vlBalance->save();

                LeaveLedger::create([
                    'employee_id' => $employee->id, 'leave_type_id' => $vlType->id, 
                    'type' => 'accrual', 
                    'amount' => $allocationAmount, 'running_balance' => $vlBalance->balance, 'created_by' => $loggedInAdmin->id,
                    'reason_code' => 'MONTHLY_ACCRUAL', 'remarks' => "Mass Accrual Allocation (+{$allocationAmount})",
                ]);

                // SL
                $slBalance = EmployeeLeaveBalance::firstOrCreate(
                    ['employee_id' => $employee->id, 'leave_type_id' => $slType->id, 'year' => $currentYear],
                    ['balance' => 0]
                );
                $slBalance->balance += $allocationAmount;
                $slBalance->save();

                LeaveLedger::create([
                    'employee_id' => $employee->id, 'leave_type_id' => $slType->id, 
                    'type' => 'accrual',  
                    'amount' => $allocationAmount, 'running_balance' => $slBalance->balance, 'created_by' => $loggedInAdmin->id,
                    'reason_code' => 'MONTHLY_ACCRUAL', 'remarks' => "Mass Accrual Allocation (+{$allocationAmount})",
                ]);
                
                $count++;
            }

            DB::commit();

            if ($count === 0) {
                return redirect()->back()->withErrors(['error' => "No employees found to allocate credits to."]);
            }

            return redirect()->back()->with('success', "Mass allocation complete. {$count} employees were credited +{$allocationAmount} to their balances.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Mass allocation failed: ' . $e->getMessage()]);
        }
    }
}