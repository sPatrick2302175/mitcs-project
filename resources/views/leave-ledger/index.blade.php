<x-app-layout>
    @php
        $leaveTypes = App\Models\LeaveType::all();

        // Get the current logged-in employee to access their hardcoded balances
        $employee = auth()->user()->employee;

        // 2. Smartly Calculate Opening Balances & Ending Balances
        $computedOpening = [];
        $currentBalances = [];

        foreach($leaveTypes as $type) {
            $typeEntries = $entries->where('leave_type_id', $type->id)->values();
            
            if ($typeEntries->isNotEmpty()) {
                // REVERSE-ENGINEER FROM THE FIRST TRANSACTION 
                $firstTx = $typeEntries->first();
                
                if ($firstTx->type === 'deduction') {
                    $computedOpening[$type->id] = $firstTx->running_balance + $firstTx->amount;
                } elseif ($firstTx->type === 'accrual') {
                    $computedOpening[$type->id] = $firstTx->running_balance - $firstTx->amount;
                } else {
                    $computedOpening[$type->id] = $firstTx->running_balance - $firstTx->amount; 
                }

                $currentBalances[$type->id] = $typeEntries->last()->running_balance;
            } else {
                // THE FIX: No transactions this month. 
                // Check if they have past ledger history. If not, fetch their actual baseline balance!
                if (isset($openingBalances[$type->id]) && $openingBalances[$type->id] > 0) {
                    $fallbackBalance = $openingBalances[$type->id];
                } else {
                    // Query the actual static balance from the database
                    $dbBalance = \App\Models\EmployeeLeaveBalance::where('employee_id', $employee->id)
                        ->where('leave_type_id', $type->id)
                        ->first();
                        
                    $fallbackBalance = $dbBalance ? $dbBalance->balance : 0;
                }

                $computedOpening[$type->id] = $fallbackBalance;
                $currentBalances[$type->id] = $fallbackBalance;
            }
        }
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Leave Balance Record') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <a href="{{ route('leave-requests.index') }}" class="inline-flex items-center text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors group">
                <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Dashboard
            </a>
            
            <!-- Navigation Header -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex justify-between items-center">
                <a href="?month={{ $date->copy()->subMonth()->format('Y-m') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-50 hover:bg-gray-100 text-gray-600 border border-gray-200/80 text-xs sm:text-sm font-bold uppercase tracking-wider rounded-xl transition-all duration-200">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                    Previous Month
                </a>
                
                <h2 class="text-base sm:text-lg font-black text-gray-800 tracking-wider uppercase">
                    {{ $date->format('F Y') }} 
                </h2>
                
                <a href="?month={{ $date->copy()->addMonth()->format('Y-m') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-50 hover:bg-gray-100 text-gray-600 border border-gray-200/80 text-xs sm:text-sm font-bold uppercase tracking-wider rounded-xl transition-all duration-200">
                    Next Month
                    <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </div>

            <!-- Ledger Sheet Card -->
            <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 overflow-hidden transition-all duration-200">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100/60">
                                <th class="py-2.5 px-3 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap">Date / Particulars</th>
                                
                                @foreach($leaveTypes as $type)
                                    <th class="py-2.5 px-3 text-xs font-bold uppercase tracking-wider text-gray-500 text-center whitespace-nowrap">
                                        {{ $type->code }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            
                            <!-- Carry-over Row -->
                            <tr class="bg-gray-50/30 hover:bg-gray-50/50 transition-colors duration-200">
                                <td class="py-2 px-3 text-xs font-bold tracking-wide uppercase text-gray-500">
                                    Total balances as of the month (Carry-over)
                                </td>
                                @foreach($leaveTypes as $type)
                                    <td class="py-2 px-3 text-center text-sm font-extrabold text-gray-700">
                                        {{ number_format($computedOpening[$type->id] ?? 0, 4, '.', '') }}
                                    </td>
                                @endforeach
                            </tr>

                            <!-- TRANSACTIONS -->
                            @forelse($entries as $entry)
                                <tr class="hover:bg-gray-50/30 transition-colors duration-200">
                                    <td class="py-3 px-3">
                                        <div class="text-sm font-bold text-gray-800 whitespace-nowrap">
                                            {{ $entry->created_at->format('M d, Y') }}
                                        </div>
                                        @if($entry->remarks)
                                            <div class="text-xs text-gray-400 font-medium mt-0.5 italic max-w-xs truncate">
                                                {{ $entry->remarks }}
                                            </div>
                                        @endif
                                    </td>
                                    
                                    @foreach($leaveTypes as $type)
                                        <td class="py-3 px-3 text-center whitespace-nowrap">
                                            @if($entry->leave_type_id == $type->id)
                                                
                                                @if($entry->type == 'deduction')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-rose-50 text-rose-600 border border-rose-100/60 shadow-sm">
                                                        -{{ number_format($entry->amount, 4, '.', '') }}
                                                    </span>
                                                @elseif($entry->type == 'accrual')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-emerald-50 text-emerald-600 border border-emerald-100/60 shadow-sm">
                                                        +{{ number_format($entry->amount, 4, '.', '') }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-blue-50 text-blue-600 border border-blue-100/60 shadow-sm">
                                                        {{ $entry->amount > 0 ? '+' : '' }}{{ number_format($entry->amount, 4, '.', '') }}
                                                    </span>
                                                @endif
                                                
                                                @php
                                                    // Dynamic Audit Trail Calculations
                                                    if ($entry->type === 'deduction') {
                                                        $prevBalance = $entry->running_balance + $entry->amount;
                                                    } else {
                                                        $prevBalance = $entry->running_balance - $entry->amount;
                                                    }
                                                @endphp

                                                <div class="mt-1 text-[10px] text-left inline-block border-t border-gray-100 pt-1 w-20">
                                                    <div class="text-gray-400 flex justify-between">
                                                        <span>Prev:</span> <span>{{ number_format($prevBalance, 4, '.', '') }}</span>
                                                    </div>
                                                    <div class="text-gray-500 font-bold flex justify-between">
                                                        <span>New:</span> <span>{{ number_format($entry->running_balance, 4, '.', '') }}</span>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-gray-200 font-normal text-sm">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($leaveTypes) + 1 }}" class="py-12 text-center">
                                        <span class="text-base font-medium text-gray-500">No transactions recorded for this calendar month.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        
                        <!-- GRAND TOTAL FOOTER BLOCK -->
                        <tfoot>
                            <tr class="bg-gray-50/80 border-t border-gray-200/80">
                                <td class="py-3 px-3 text-xs font-bold uppercase tracking-wider text-gray-600">
                                    Grand Total (Ending Balances)
                                </td>
                                @foreach($leaveTypes as $type)
                                    <td class="py-3 px-3 text-center text-base font-black text-[#F2A455]">
                                        {{ number_format($currentBalances[$type->id] ?? 0, 4, '.', '') }}
                                    </td>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>