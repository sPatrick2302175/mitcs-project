<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Division;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * READ: Show all employees in a list/table view
     */
    public function index()
    {
        $loggedInAdmin = auth()->user();

        // 1. Fetch employees based on role (Filtering out the Master Admin account '000000')
        if ($loggedInAdmin->is_admin === \App\Models\User::ROLE_SUPER_ADMIN) {
            // Super Admin gets everyone EXCEPT the system utility account
            $employeesQuery = Employee::with(['department', 'division', 'user'])
                ->where('employee_id_number', '!=', '0000000')
                ->get();
        } else {
            // Dept Admin gets only their department
            $departmentId = $loggedInAdmin->employee ? $loggedInAdmin->employee->department_id : null;

            $employeesQuery = Employee::with(['department', 'division', 'user'])
                ->where('department_id', $departmentId)
                ->where('employee_id_number', '!=', '0000000') // Safety filter
                ->get();
        }

        // 2. Group the results by the department name
        $groupedEmployees = $employeesQuery->groupBy(function($employee) {
            return $employee->department ? $employee->department->department_name : 'Unassigned Department';
        });

        return view('employees.index', compact('groupedEmployees'));
    }

    /**
     * CREATE: Show the HTML form to add a new employee
     */
    public function create()
    {
        // We need to fetch departments and divisions so the admin can pick them in a dropdown menu
        $departments = Department::all();
        $divisions = Division::all();
        
        return view('employees.create', compact('departments', 'divisions'));
    }

    /**
     * CREATE: Save the form data to the database
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'department_id' => 'required|exists:departments,id',
            'employee_id_number' => 'required|string|unique:employees,employee_id_number',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'leave_credits' => 'nullable|integer',
        ]);

        Employee::create($validatedData);

        return redirect()->route('employees.index')->with('success', 'Employee created successfully!');
    }

    /**
     * UPDATE: Show the form to edit an existing employee
     */
    public function edit(string $id)
    {
        $employee = Employee::findOrFail($id);
        $departments = Department::all();
        $divisions = Division::all();

        return view('employees.edit', compact('employee', 'departments', 'divisions'));
    }

    /**
     * UPDATE: Save the edited changes to the database
     */
    public function update(Request $request, string $id)
    {
        $employee = Employee::findOrFail($id);

        $validatedData = $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'department_id' => 'required|exists:departments,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'leave_credits' => 'required|integer',
        ]);

        $employee->update($validatedData);

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully!');
    }

    /**
     * DELETE: Remove an employee
     */
    public function destroy(string $id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully!');
    }

    /**
     * UPDATE: Change the user's role (Super Admin only)
     */

    public function changeRole(Request $request, string $id)
    {
        // 1. Double-check security
        if (auth()->user()->is_admin !== \App\Models\User::ROLE_SUPER_ADMIN) {
            abort(403, 'Unauthorized action.');
        }

        // 2. Validate incoming role
        $request->validate([
            'role' => 'required|integer|in:0,1,2',
        ]);

        // 3. Find the employee and their user
        $employee = Employee::with('user')->findOrFail($id);

        if (!$employee->user) {
            return redirect()->back()->withErrors(['error' => 'Cannot change role: This employee does not have a registered user account yet.']);
        }

        // --- NEW RESTRICTION LOGIC ---
        if ($request->role == \App\Models\User::ROLE_DEPT_ADMIN) {
            // Check if this department already has an admin assigned
            $existingAdmin = Employee::where('department_id', $employee->department_id)
                ->where('id', '!=', $employee->id) // Ignore the person we are currently updating
                ->whereHas('user', function ($query) {
                    $query->where('is_admin', \App\Models\User::ROLE_DEPT_ADMIN);
                })
                ->first();

            // If an admin is found, reject the change and send back an error message
            if ($existingAdmin) {
                $errorMsg = 'Cannot assign role: ' . $existingAdmin->first_name . ' ' . $existingAdmin->last_name . ' is already the admin for this department. Please demote them to an Employee first.';
                return redirect()->back()->withErrors(['error' => $errorMsg]);
            }
        }
        // -----------------------------

        // 4. Update the role if it passes the check
        $employee->user->update([
            'is_admin' => $request->role
        ]);

        return redirect()->back()->with('success', 'User role updated successfully!');
    }
}