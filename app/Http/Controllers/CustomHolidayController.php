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
        return view('custom_holidays.index', compact('holidays')); // Ensure this matches your actual view path
    }

    /**
     * Store newly created custom holiday(s) in storage.
     */
    public function store(Request $request)
    {
        // Validate that 'dates' exists
        $request->validate([
            'name' => 'required|string',
            'dates' => 'required|string',
            'type' => 'required|string',
        ]);

        // Split the comma-separated string into an array
        $dateArray = explode(', ', $request->dates);

        // Loop through each selected date and create a record
        foreach ($dateArray as $singleDate) {
            CustomHoliday::create([
                'name' => $request->name,
                'type' => $request->type,
                'is_half_day' => $request->has('is_half_day'),
                'date' => $singleDate,
            ]);
        }

        return redirect()->back()->with('success', 'Holidays added successfully!');
    }

    /**
     * Show the form for editing the specified custom holiday.
     */
    public function edit(CustomHoliday $customHoliday)
    {
        return view('admin.custom_holidays.edit', compact('customHoliday'));
    }

    /**
     * Update the specified custom holiday in storage.
     */
    public function update(Request $request, CustomHoliday $customHoliday)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'type' => 'required|in:regular,custom',
        ]);

        $customHoliday->update([
            'name' => $request->name,
            'date' => $request->date,
            'type' => $request->type,
            'is_half_day' => $request->has('is_half_day'),
        ]);

        return redirect()->route('admin.custom-holidays.index')
                         ->with('success', 'Holiday updated successfully!');
    }

    /**
     * Remove the specified custom holiday from storage.
     */
    public function destroy(CustomHoliday $customHoliday)
    {
        $customHoliday->delete();

        return redirect()->route('admin.custom-holidays.index')
                         ->with('success', 'Holiday removed successfully.');
    }
}