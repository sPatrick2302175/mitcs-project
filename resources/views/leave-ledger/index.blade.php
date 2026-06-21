<x-app-layout>
    @php
        // 1. Explicitly filter out only the 4 leave types you want to track
        $targetCodes = ['VL', 'SL', 'FL', 'SPL'];
        $leaveTypes = App\Models\LeaveType::whereIn('code', $targetCodes)
            ->get()
            ->sortBy(function($type) use ($targetCodes) {
                return array_search($type->code, $targetCodes);
            });

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
                            <!-- Increased Header Text Sizes -->
                            <tr class="bg-gray-50/50 border-b border-gray-100/60">
                                <th class="py-4 px-6 text-xs font-bold uppercase tracking-wider text-gray-500">Date / Particulars</th>
                                <th class="py-4 px-6 text-xs font-bold uppercase tracking-wider text-gray-500 text-center">VL (Vacation)</th>
                                <th class="py-4 px-6 text-xs font-bold uppercase tracking-wider text-gray-500 text-center">SL (Sick)</th>
                                <th class="py-4 px-6 text-xs font-bold uppercase tracking-wider text-gray-500 text-center">MANDATORY (FL)</th>
                                <th class="py-4 px-6 text-xs font-bold uppercase tracking-wider text-gray-500 text-center">SPL (Special Priv.)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            
                            <!-- Carry-over Row -->
                            <tr class="bg-gray-50/30 hover:bg-gray-50/50 transition-colors duration-200">
                                <td class="py-4 px-6 text-sm font-bold tracking-wide uppercase text-gray-600">
                                    Total balances as of the month (Carry-over)
                                </td>
                                @foreach($leaveTypes as $type)
                                    <!-- Increased Carry-over number size -->
                                    <td class="py-4 px-6 text-center text-base font-extrabold text-gray-800">
                                        {{ number_format($computedOpening[$type->id] ?? 0, 3) }}
                                    </td>
                                @endforeach
                            </tr>

                            <!-- TRANSACTIONS -->
                            @forelse($entries as $entry)
                                <tr class="hover:bg-gray-50/30 transition-colors duration-200">
                                    <td class="py-4 px-6">
                                        <!-- Increased Date Size -->
                                        <div class="text-base font-bold text-gray-800 whitespace-nowrap">
                                            {{ $entry->created_at->format('M d, Y') }}
                                        </div>
                                        @if($entry->remarks)
                                            <!-- Increased Remarks Size -->
                                            <div class="text-sm text-gray-500 font-medium mt-1 italic">
                                                {{ $entry->remarks }}
                                            </div>
                                        @endif
                                    </td>
                                    
                                    @foreach($leaveTypes as $type)
                                        <td class="py-4 px-6 text-center whitespace-nowrap">
                                            @if($entry->leave_type_id == $type->id)
                                                <!-- Increased Badge Text & Padding -->
                                                @if($entry->type == 'deduction')
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs sm:text-sm font-bold bg-rose-50 text-rose-600 border border-rose-100/60 shadow-sm">
                                                        -{{ number_format($entry->amount, 1) }}
                                                    </span>
                                                @elseif($entry->type == 'accrual')
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs sm:text-sm font-bold bg-emerald-50 text-emerald-600 border border-emerald-100/60 shadow-sm">
                                                        +{{ number_format($entry->amount, 3) }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs sm:text-sm font-bold bg-blue-50 text-blue-600 border border-blue-100/60 shadow-sm">
                                                        {{ $entry->amount > 0 ? '+' : '' }}{{ number_format($entry->amount, 3) }}
                                                    </span>
                                                @endif
                                                
                                                <!-- Increased Balance Text -->
                                                <span class="block text-xs text-gray-500 font-medium mt-1.5">
                                                    Bal: {{ number_format($entry->running_balance, 3) }}
                                                </span>
                                            @else
                                                <span class="text-gray-300 font-normal text-base">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center">
                                        <span class="text-base font-medium text-gray-500">No transactions recorded for this calendar month.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        
                        <!-- GRAND TOTAL FOOTER BLOCK -->
                        <tfoot>
                            <tr class="bg-gray-50/80 border-t border-gray-200/80">
                                <td class="py-5 px-6 text-sm font-bold uppercase tracking-wider text-gray-700">
                                    Grand Total (Ending Balances)
                                </td>
                                @foreach($leaveTypes as $type)
                                    <!-- Enlarged Grand Total Numbers -->
                                    <td class="py-5 px-6 text-center text-lg font-black text-[#F2A455]">
                                        {{ number_format($currentBalances[$type->id] ?? 0, 3) }}
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