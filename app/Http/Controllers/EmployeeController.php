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

        // 1. Check if Super Admin
        if ($loggedInAdmin->is_admin === \App\Models\User::ROLE_SUPER_ADMIN) {
            // Fetch everyone, and include their user, department, and division data
            $employees = Employee::with(['department', 'division', 'user'])->get();
        } 
        // 2. Otherwise, they are a Dept Admin
        else {
            // Fetch ONLY employees matching the Dept Admin's department
            $employees = Employee::with(['department', 'division', 'user'])
                ->where('department_id', $loggedInAdmin->department_id)
                ->get();
        }

        return view('employees.index', compact('employees'));
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
}