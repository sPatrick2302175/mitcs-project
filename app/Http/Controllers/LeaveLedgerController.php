<?php

namespace App\Http\Controllers;

use App\Models\LeaveLedger;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeaveLedgerController extends Controller
{
    public function myLedger(Request $request)
    {
        $user = auth()->user();
        $requestedEmployeeId = $request->get('employee_id');

        // SECURITY CHECK: 
        // If an admin (is_admin !== 0) clicks a specific employee's ledger button
        if ($requestedEmployeeId && $user->is_admin !== 0) {
            $employee = \App\Models\Employee::findOrFail($requestedEmployeeId);
        } else {
            // Otherwise, rigidly lock it to the logged-in user's profile
            $employee = $user->employee;
        }

        $month = $request->get('month', now()->format('Y-m'));
        $date = Carbon::parse($month);

        // Fetch ledger entries for the specific month securely tied to $employee
        $entries = LeaveLedger::where('employee_id', $employee->id)
            ->whereMonth('created_at', $date->month)
            ->whereYear('created_at', $date->year)
            ->orderBy('created_at', 'asc')
            ->get();

        // Get the running balance as of the end of the PREVIOUS month
        $openingBalances = [];
        foreach (LeaveType::all() as $type) {
            $lastEntry = LeaveLedger::where('employee_id', $employee->id)
                ->where('leave_type_id', $type->id)
                ->where('created_at', '<', $date->startOfMonth())
                ->latest()
                ->first();
            $openingBalances[$type->id] = $lastEntry ? $lastEntry->running_balance : 0;
        }

        // IMPORTANT: Add 'employee' to the compact() list so the Blade file can use it
        return view('leave-ledger.index', compact('entries', 'date', 'openingBalances', 'employee'));
    }
}