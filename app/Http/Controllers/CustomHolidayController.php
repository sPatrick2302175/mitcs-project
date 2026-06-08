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
        return view('admin.custom_holidays.index', compact('holidays')); // Ensure this matches your actual view path
    }

    /**
     * Store newly created custom holiday(s) in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'dates' => 'required|string', // Flatpickr sends comma-separated dates
            'type' => 'required|in:regular,custom', 
        ]);

        // Break the comma-separated string into an array
        $dateArray = explode(',', $request->dates);
        $addedCount = 0;
        
        // Capture the boolean value from the checkbox
        $isHalfDay = $request->has('is_half_day');

        foreach ($dateArray as $date) {
            $cleanDate = trim($date);
            
            // Gracefully check if this date is already logged to prevent crashing
            $exists = CustomHoliday::where('date', $cleanDate)->exists();
            
            if (!$exists) {
                CustomHoliday::create([
                    'name' => $request->name,
                    'date' => $cleanDate,
                    'type' => $request->type, 
                    'is_half_day' => $isHalfDay, // <-- SAVES HALF DAY STATUS TO DB
                ]);
                $addedCount++;
            }
        }

        // Customize the success message based on whether it added multiple days, one day, or none (if all were duplicates)
        if ($addedCount > 0) {
            $message = "{$addedCount} holiday(s) added successfully! They will now be calculated correctly on leave requests.";
        } else {
            $message = "No new holidays added. The selected date(s) already exist in the system.";
        }

        // Make sure this route matches your web.php definition
        return redirect()->route('admin.custom-holidays.index')->with('success', $message);
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