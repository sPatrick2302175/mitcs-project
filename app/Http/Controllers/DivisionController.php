<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Department;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    public function index()
    {
        // Fetch departments and their divisions (Plural!)
        $departments = \App\Models\Department::with('divisions')->get();
        
        return view('divisions.index', compact('departments'));
    }

    public function create()
    {
        $departments = \App\Models\Department::all();
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
        $departments = \App\Models\Department::all(); 
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