<?php

namespace App\Http\Controllers;

 use App\Models\User;
 use App\Models\Department;
 use Illuminate\Http\Request;

 class UserController extends Controller
 {
    public function index()
    {
        $users = User::with('department')->get();
        return view('users.index', compact('users'));
    }

    public function edit(User $user)
    {
        $departments = Department::all();
        return view('users.edit', compact('user', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'is_admin' => 'required|integer|in:0,1,2,3', // 👈 Updated to accept Department Head (3)
            // Department field is required if the user is an Admin Officer (1) or Dept Head (3)
            'department_id' => 'required_if:is_admin,1|required_if:is_admin,3|nullable|exists:departments,id',
        ]);

        // Retain department mapping details for both roles 1 and 3
        $departmentId = in_array($request->is_admin, [User::ROLE_ADMIN_OFFICER, User::ROLE_DEPT_HEAD]) 
            ? $request->department_id 
            : null;

        $user->update([
            'is_admin' => $request->is_admin,
            'department_id' => $departmentId,
        ]);

        return redirect()->route('users.index')->with('success', 'User role updated successfully.');
    }
}