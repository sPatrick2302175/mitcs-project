<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('employee.division.department')->get();
        return view('users.index', compact('users'));
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'is_admin' => 'required|integer|in:0,1,2,3',
        ]);

        $user->update([
            'is_admin' => $request->is_admin,
        ]);

        return redirect()->route('users.index')->with('success', 'User role updated successfully.');
    }
}