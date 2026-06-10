<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Division;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $loggedInAdmin = auth()->user();

        // Fetch employees based on role excluding super admin
        if ($loggedInAdmin->is_admin === User::ROLE_SUPER_ADMIN) {
            $employeesQuery = Employee::with(['department', 'division', 'user'])
                ->where('employee_id_number', '!=', '0000000')
                ->get();
        } else {
            // Both Admin Officers (1) and Dept Heads (3) fall here and safely get only their department's team
            $departmentId = $loggedInAdmin->employee ? $loggedInAdmin->employee->department_id : null;

            $employeesQuery = Employee::with(['department', 'division', 'user'])
                ->where('department_id', $departmentId)
                ->where('employee_id_number', '!=', '0000000')
                ->get();
        }

        // Group the table by the department name
        $groupedEmployees = $employeesQuery->groupBy(function($employee) {
            return $employee->department ? $employee->department->department_name : 'Unassigned Department';
        });

        return view('employees.index', compact('groupedEmployees'));
    }

    public function create()
    {
        $departments = Department::where('code', '!=', 'SYSTEM-ADMIN')->get();
        $divisions = Division::all();
        
        return view('employees.create', compact('departments', 'divisions'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'department_id' => 'required|exists:departments,id',
            'employee_id_number' => 'required|string|unique:employees,employee_id_number|max:10',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:1',
            'position' => 'required|string|max:255',
            'position_code' => 'required|string|max:20',
            
            'vacation_leave_balance' => 'required|numeric|min:0',
            'sick_leave_balance' => 'required|numeric|min:0',
            'mandatory_leave_balance' => 'required|numeric|min:0',
            'special_privilege_leave_balance' => 'required|numeric|min:0',
            'special_emergency_leave_balance' => 'required|numeric|min:0',
        ]);

        Employee::create($validatedData);
        return redirect()->route('employees.index')->with('success', 'Employee created successfully!');
    }

    public function show(string $id)
    {
        $employee = Employee::with(['department', 'division', 'user'])->findOrFail($id);
        return view('employees.show', compact('employee'));
    }

    public function edit(string $id)
    {
        $employee = Employee::findOrFail($id);
        $departments = Department::where('code', '!=', 'SYSTEM-ADMIN')->get();
        $divisions = Division::all();

        return view('employees.edit', compact('employee', 'departments', 'divisions'));
    }

    public function update(Request $request, string $id)
    {
        $employee = Employee::findOrFail($id);

        $validatedData = $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'department_id' => 'required|exists:departments,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:1',
            'position' => 'required|string|max:255',
            'position_code' => 'required|string|max:20',

            'vacation_leave_balance' => 'required|numeric|min:0',
            'sick_leave_balance' => 'required|numeric|min:0',
            'mandatory_leave_balance' => 'required|numeric|min:0',
            'special_privilege_leave_balance' => 'required|numeric|min:0',
            'special_emergency_leave_balance' => 'required|numeric|min:0',
        ]);

        $employee->update($validatedData);
        return redirect()->route('employees.index')->with('success', 'Employee updated successfully!');
    }

    public function destroy(string $id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully!');
    }

    // 🔐 REWRITTEN: Role adjustment security engine
    public function changeRole(Request $request, string $id)
    {
        $currentUser = auth()->user();

        // 1. Authorization Check: Only Super Admins and Department Heads can change roles
        if (!in_array($currentUser->is_admin, [User::ROLE_SUPER_ADMIN, User::ROLE_DEPT_HEAD])) {
            abort(403, 'Unauthorized action.');
        }

        $employee = Employee::with('user')->findOrFail($id);

        if (!$employee->user) {
            return redirect()->back()->withErrors(['error' => 'Cannot change role: This employee does not have a registered user account yet.']);
        }

        // 2. Department Boundary Check: Department Heads can ONLY touch their own department's employees
        if ($currentUser->is_admin === User::ROLE_DEPT_HEAD) {
            $myDepartmentId = $currentUser->employee ? $currentUser->employee->department_id : null;
            if ($employee->department_id !== $myDepartmentId) {
                abort(403, 'Unauthorized action. You can only alter roles for members within your own department.');
            }
        }

        // 3. Dynamic Validation: 
        // Super Admins can assign any role (0,1,2,3)
        // Department Heads can only toggle between Employee (0) and Admin Officer (1)
        $allowedRoles = $currentUser->is_admin === User::ROLE_SUPER_ADMIN ? '0,1,2,3' : '0,1';

        $request->validate([
            'role' => 'required|integer|in:' . $allowedRoles,
        ]);

        // 4. Cap Limit: Check if this department already has an Admin Officer assigned
        if ($request->role == User::ROLE_ADMIN_OFFICER) {
            $existingAdmin = Employee::where('department_id', $employee->department_id)
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

        // 5. Cap Limit: Check if this department already has a Department Head assigned (For Super Admin changes)
        if ($request->role == User::ROLE_DEPT_HEAD) {
            $existingHead = Employee::where('department_id', $employee->department_id)
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

        // Commit role update
        $employee->user->update([
            'is_admin' => $request->role
        ]);

        return redirect()->back()->with('success', 'User role updated successfully!');
    }
}