<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    public function index()
    {
        $divisions = Division::all();
        return view('divisions.index', compact('divisions'));
    }

    public function create()
    {
        return view('divisions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'division_name' => 'required|string|max:255',
            'code' => 'nullable|string|unique:divisions,code|max:50',
        ]);

        Division::create($validated);

        return redirect()->route('divisions.index')->with('success', 'Division created successfully!');
    }

    public function edit(string $id)
    {
        $division = Division::findOrFail($id);
        return view('divisions.edit', compact('division'));
    }

    public function update(Request $request, string $id)
    {
        $division = Division::findOrFail($id);

        $validated = $request->validate([
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