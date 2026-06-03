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

        //Fetch employees based on role excluding super admin
        if ($loggedInAdmin->is_admin === User::ROLE_SUPER_ADMIN) {
            $employeesQuery = Employee::with(['department', 'division', 'user'])
                ->where('employee_id_number', '!=', '0000000')
                ->get();
        } else {
            // Department Admin gets only their department
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
        // fetch departments and divisions. Excludes super admin
        $departments = Department::where('code', '!=', 'SYSTEM-ADMIN')->get();
        $divisions = Division::all();
        
        return view('employees.create', compact('departments', 'divisions'));
    }

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
            'position' => 'required|string|max:255',
            'leave_credits' => 'required|integer',
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

    public function changeRole(Request $request, string $id)
    {
        // security check
        if (auth()->user()->is_admin !== User::ROLE_SUPER_ADMIN) {
            abort(403, 'Unauthorized action.');
        }

        // validate incoming role
        $request->validate([
            'role' => 'required|integer|in:0,1,2',
        ]);

        // find the employee and their user
        $employee = Employee::with('user')->findOrFail($id);

        if (!$employee->user) {
            return redirect()->back()->withErrors(['error' => 'Cannot change role: This employee does not have a registered user account yet.']);
        }

        // RESTRICTION LOGIC 
        if ($request->role == User::ROLE_DEPT_ADMIN) {
            // Check if this department already has an admin assigned
            $existingAdmin = Employee::where('department_id', $employee->department_id)
                ->where('id', '!=', $employee->id) // Ignore the person we are currently updating
                ->whereHas('user', function ($query) {
                    $query->where('is_admin', User::ROLE_DEPT_ADMIN);
                })
                ->first();

            // If an admin is found, reject the change and send back an error message
            if ($existingAdmin) {
                $errorMsg = 'Cannot assign role: ' . $existingAdmin->first_name . ' ' . $existingAdmin->last_name . ' is already the admin for this department. Please demote them to an Employee first.';
                return redirect()->back()->withErrors(['error' => $errorMsg]);
            }
        }

        // if pass the check update the role
        $employee->user->update([
            'is_admin' => $request->role
        ]);

        return redirect()->back()->with('success', 'User role updated successfully!');
    }
}