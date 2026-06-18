<?php

namespace App\Http\Controllers;

use App\Models\CustomHoliday;
use Illuminate\Http\Request;

class CustomHolidayController extends Controller
{
    /**
     * Display a listing of the custom holidays.
     */
    public function index()
    {
        // Sorted by date ascending so upcoming holidays appear in chronological order
        $holidays = CustomHoliday::orderBy('date', 'asc')->get();
        return view('custom_holidays.index', compact('holidays')); 
    }

    /**
     * Store newly created custom holiday(s) in storage.
     */
    public function store(Request $request)
    {
        // Validate that 'dates' exists
        $request->validate([
            'name' => 'required|string|max:255',
            'dates' => 'required|string',
            'type' => 'required|string',
        ]);

        $dateArray = explode(', ', $request->dates);

        // Loop through each selected date and create a record
        foreach ($dateArray as $singleDate) {
            CustomHoliday::create([
                'name'        => $request->name,
                'type'        => $request->type,
                'is_half_day' => $request->has('is_half_day'),
                'is_regular'  => $request->has('is_regular'), // Captures the recurring toggle
                'is_active'   => true, // Active by default on creation
                'date'        => $singleDate,
            ]);
        }

        return redirect()->back()->with('success', 'Holidays added successfully!');
    }

    /**
     * Show the form for editing the specified custom holiday.
     */
   
    public function edit($id)
    {
        // Find the holiday or throw a 404 error if it doesn't exist
        $customHoliday = CustomHoliday::findOrFail($id);
        
        // Load the view we just created and pass the data to it
        return view('custom_holidays.edit', compact('customHoliday'));
    }

    /**
     * Update the specified holiday in storage.
     */
    public function update(Request $request, $id)
    {
        // 1. Validate the incoming data
        $request->validate([
            'date' => 'required|date',
            'name' => 'required|string|max:255',
            'type' => 'required|in:custom,regular',
        ]);

        // Find the exact record
        $customHoliday = CustomHoliday::findOrFail($id);

        // Update the record
        // Note: checkboxes only send data if checked, used $request->has() to map them to booleans
        $customHoliday->update([
            'date' => $request->date,
            'name' => $request->name,
            'type' => $request->type,
            'is_half_day' => $request->has('is_half_day'),
            'is_regular' => $request->has('is_regular'),
        ]);

        // Redirect back to the masterlist with a success message
        return redirect()->route('admin.custom-holidays.index')
                         ->with('success', 'Calendar rule updated successfully!');
    }

    // Toggles the holiday viewable state for users without deleting the record
    public function toggleStatus(CustomHoliday $customHoliday)
    {
        $customHoliday->update([
            'is_active' => !$customHoliday->is_active
        ]);

        $status = $customHoliday->is_active ? 'enabled' : 'disabled';
        return redirect()->back()->with('success', "Holiday has been {$status} successfully.");
    }

    /**
     * Remove the specified custom holiday from storage.
     */
    public function destroy(CustomHoliday $customHoliday)
    {
        $customHoliday->delete();

        return redirect()->route('admin.custom-holidays.index')->with('success', 'Holiday completely removed.');
    }
}