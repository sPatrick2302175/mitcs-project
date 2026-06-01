<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Display list of all users and their roles
    public function index()
    {
        $users = User::with('department')->get();
        return view('users.index', compact('users'));
    }

    // Show form to edit a specific user's role
    public function edit(User $user)
    {
        $departments = Department::all();
        return view('users.edit', compact('user', 'departments'));
    }

    // Save the changes
    public function update(Request $request, User $user)
    {
        $request->validate([
            'is_admin' => 'required|integer|in:0,1,2',
            // Department is required ONLY if they are being made a Department Admin (1)
            'department_id' => 'required_if:is_admin,1|nullable|exists:departments,id',
        ]);

        // If they aren't a Dept Admin, clear out their department assignment
        $departmentId = $request->is_admin == User::ROLE_DEPT_ADMIN ? $request->department_id : null;

        $user->update([
            'is_admin' => $request->is_admin,
            'department_id' => $departmentId,
        ]);

        return redirect()->route('users.index')->with('success', 'User role updated successfully.');
    }
}