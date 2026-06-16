<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Department;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    public function index()
    {
        $loggedInAdmin = auth()->user();
        
        if ($loggedInAdmin->is_admin === \App\Models\User::ROLE_SUPER_ADMIN) {
            $departments = Department::with('divisions')
                ->where('code', '!=', 'SYSTEM-ADMIN')
                ->get();
        } else {
            // FIX: Safely route through the division to get the department ID
            $departmentId = $loggedInAdmin->employee?->division?->department_id;

            $departments = Department::with('divisions')
                ->where('id', $departmentId)
                ->where('code', '!=', 'SYSTEM-ADMIN')
                ->get();
        }
       
        return view('divisions.index', compact('departments'));
    }

    public function create()
    {
        $departments = Department::where('code', '!=', 'SYSTEM-ADMIN')->get();
        return view('divisions.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'division_name' => 'required|string|max:255',
            'code' => 'nullable|string|unique:divisions,code|max:50',
        ]);

        Division::create($validated);
        return redirect()->route('divisions.index')->with('success', 'Division created successfully!');
    }

    public function edit(string $id)
    {
        $division = Division::findOrFail($id);
        $departments = Department::where('code', '!=', 'SYSTEM-ADMIN')->get();
        return view('divisions.edit', compact('division', 'departments'));
    }

    public function update(Request $request, string $id)
    {
        $division = Division::findOrFail($id);
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'division_name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:divisions,code,' . $division->id,
        ]);

        $division->update($validated);
        return redirect()->route('divisions.index')->with('success', 'Division updated successfully!');
    }

    public function destroy(string $id)
    {
        $division = Division::findOrFail($id);
        $division->delete();

        return redirect()->route('divisions.index')->with('success', 'Division deleted successfully!');
    }
}