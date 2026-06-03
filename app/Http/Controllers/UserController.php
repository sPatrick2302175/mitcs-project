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
            // required 1 admin per department
            'department_id' => 'required_if:is_admin,1|nullable|exists:departments,id',
        ]);

        // if they user is not a Dept Admin, clear out their department features
        $departmentId = $request->is_admin == User::ROLE_DEPT_ADMIN ? $request->department_id : null;

        $user->update([
            'is_admin' => $request->is_admin,
            'department_id' => $departmentId,
        ]);

        return redirect()->route('users.index')->with('success', 'User role updated successfully.');
    }
}