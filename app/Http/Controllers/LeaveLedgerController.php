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
        $employee = auth()->user()->employee;
        $month = $request->get('month', now()->format('Y-m'));
        $date = Carbon::parse($month);

        // Fetch ledger entries for the specific month
        $entries = LeaveLedger::where('employee_id', $employee->id)
            ->whereMonth('created_at', $date->month)
            ->whereYear('created_at', $date->year)
            ->orderBy('created_at', 'asc')
            ->get();

        // Get the running balance as of the end of the PREVIOUS month
        $previousMonth = $date->copy()->subMonth();
        $openingBalances = [];
        foreach (LeaveType::all() as $type) {
            $lastEntry = LeaveLedger::where('employee_id', $employee->id)
                ->where('leave_type_id', $type->id)
                ->where('created_at', '<', $date->startOfMonth())
                ->latest()
                ->first();
            $openingBalances[$type->id] = $lastEntry ? $lastEntry->running_balance : 0;
        }

        return view('leave-ledger.index', compact('entries', 'date', 'openingBalances'));
    }
}