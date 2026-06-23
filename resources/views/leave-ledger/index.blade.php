<x-app-layout>
    @php
        $leaveTypes = App\Models\LeaveType::all();

        // Get the current logged-in employee to access their hardcoded balances
        // $employee = auth()->user()->employee;

        // 2. Smartly Calculate Opening Balances & Ending Balances
        $computedOpening = [];
        $currentBalances = [];

        foreach($leaveTypes as $type) {
            $typeEntries = $entries->where('leave_type_id', $type->id)->values();
            
            if ($typeEntries->isNotEmpty()) {
                
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
            <div class="mb-6">
                @if(request()->has('employee_id'))
                    <!-- If accessed via Employee Profile -->
                    <a href="{{ route('employees.show', [
                            'employee' => $employee->id, 
                            'from' => request()->query('from'), 
                            'request_id' => request()->query('request_id')
                        ]) }}" 
                       class="inline-flex items-center text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors group">
                        <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Back to {{ $employee->first_name ?? 'Employee' }}'s Profile
                    </a>
                @else
                    <!-- If accessed via User's own Dashboard -->
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors group">
                        <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Back to Leave Dashboard
                    </a>
                @endif
            </div>

            <!--Leave type legend-->
            <div class="mb-6 px-2">
                <button type="button" onclick="toggleLegend()" class="text-xs font-bold text-gray-400 hover:text-[#df9344] uppercase tracking-wider transition-colors inline-flex items-center group">
                    <svg id="legendIcon" class="w-4 h-4 mr-1.5 text-gray-400 group-hover:text-[#df9344] transition-all duration-300 transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                    <span id="legendBtnText">View Leave Codes Legend</span>
                </button>

                <div id="leaveLegend" class="max-h-0 opacity-0 overflow-hidden transition-all duration-500 ease-in-out">
                    <div class="mt-3 p-5 bg-gray-50/80 rounded-2xl border border-gray-200/60 shadow-sm">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                            @foreach($leaveTypes as $type)
                                <div id="legend-box-{{ $loop->index }}" class="flex items-center p-2.5 rounded-xl hover:bg-white hover:shadow-sm transition-all duration-200 border border-transparent hover:border-gray-200/60 group/item cursor-default">
                                    <span class="inline-flex items-center justify-center px-2.5 py-1.5 rounded-lg bg-[#F2A455]/10 text-[#df9344] border border-[#F2A455]/20 font-black text-xs tracking-wider min-w-[3.5rem] shadow-sm group-hover/item:bg-[#F2A455] group-hover/item:text-white transition-colors duration-200">
                                        {{ $type->code ?? 'N/A' }}
                                    </span>
                                    
                                    <span class="text-gray-500 text-sm font-semibold ml-3 leading-tight group-hover/item:text-gray-800 transition-colors duration-200">
                                        {{ $type->leave_type_name ?? 'Unknown' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            
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

            

            <!--Toggle table-->
            <div class="flex items-center justify-end mt-6 mb-4 px-2">
                <div class="inline-flex bg-gray-100/80 p-1 rounded-xl shadow-inner border border-gray-200/60">
                    
                    <button type="button" id="btnMatrix" onclick="forceView('matrix')" 
                        class="px-4 py-2 text-sm font-bold rounded-lg transition-all duration-200 bg-white text-[#df9344] shadow-sm border border-gray-200/50 inline-flex items-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        Matrix View
                    </button>
                    
                    <button type="button" id="btnList" onclick="forceView('list')" 
                        class="px-4 py-2 text-sm font-semibold rounded-lg transition-all duration-200 text-gray-500 hover:text-gray-700 border border-transparent inline-flex items-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        List View
                    </button>
                    
                </div>
            </div>
            

            <!-- TRADITIONAL MATRIX VIEW (Hidden by default) -->
            <div id="matrixView" class="block">
            <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 overflow-hidden transition-all duration-200">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100/60">
                                <th class="py-2.5 px-3 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap">Date / Particulars</th>
                                
                                @foreach($leaveTypes as $type)
                                    <!-- Added onclick, cursor-pointer, and dynamic class -->
                                    <th onclick="highlightColumn({{ $loop->index }})" title="Click to highlight {{ $type->code }} column"
                                        class="matrix-col-{{ $loop->index }} py-2.5 px-3 text-xs font-bold uppercase tracking-wider text-gray-500 text-center whitespace-nowrap cursor-pointer hover:bg-gray-200 hover:text-gray-800 transition-colors duration-200">
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
                                    <!-- Added dynamic class -->
                                    <td class="matrix-col-{{ $loop->index }} py-2 px-3 text-center text-sm font-extrabold text-gray-700 transition-colors duration-200">
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
                                        <!-- Added dynamic class -->
                                        <td class="matrix-col-{{ $loop->index }} py-3 px-3 text-center whitespace-nowrap transition-colors duration-200">
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
                                    <!-- Added dynamic class -->
                                    <td class="matrix-col-{{ $loop->index }} py-3 px-3 text-center text-base font-black text-[#F2A455] transition-colors duration-200">
                                        {{ number_format($currentBalances[$type->id] ?? 0, 4, '.', '') }}
                                    </td>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            </div>

            <!-- Ledger Sheet Card -->
            <div id="listView" class="hidden">
            <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 overflow-hidden transition-all duration-200">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/80 border-b border-gray-100/80">
                                <th class="py-3 px-4 text-xs font-extrabold uppercase tracking-widest text-gray-400 whitespace-nowrap">Date</th>
                                <th class="py-3 px-4 text-xs font-extrabold uppercase tracking-widest text-gray-400">Particulars / Remarks</th>
                                <th class="py-3 px-4 text-xs font-extrabold uppercase tracking-widest text-gray-400 text-center">Leave Category</th>
                                <th class="py-3 px-4 text-xs font-extrabold uppercase tracking-widest text-gray-400 text-center">Transaction Amount</th>
                                <th class="py-3 px-4 text-xs font-extrabold uppercase tracking-widest text-gray-400 text-right">Running Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50/80">
                            
                            <!-- TRANSACTIONS -->
                            @forelse($entries as $entry)
                                <tr class="hover:bg-gray-50/50 transition-colors duration-200">
                                    
                                    <!-- Date -->
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-800">
                                            {{ $entry->created_at->format('M d, Y') }}
                                        </div>
                                        <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mt-0.5">
                                            {{ $entry->created_at->format('h:i A') }}
                                        </div>
                                    </td>
                                    
                                    <!-- Particulars -->
                                    <td class="py-4 px-4">
                                        <div class="text-sm text-gray-600 font-medium max-w-md truncate">
                                            {{ $entry->remarks ?? 'Standard Ledger Transaction' }}
                                        </div>
                                    </td>
                                    
                                    <!-- Leave Type -->
                                    <td class="py-4 px-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black uppercase tracking-wider bg-gray-100 text-gray-600 border border-gray-200/60 shadow-sm">
                                            {{ $entry->leaveType->code ?? 'N/A' }}
                                        </span>
                                    </td>
                                    
                                    <!-- Amount -->
                                    <td class="py-4 px-4 text-center">
                                        @if($entry->type == 'deduction')
                                            <span class="inline-flex items-center text-sm font-black text-rose-600">
                                                -{{ number_format($entry->amount, 4, '.', '') }}
                                            </span>
                                        @elseif($entry->type == 'accrual')
                                            <span class="inline-flex items-center text-sm font-black text-emerald-600">
                                                +{{ number_format($entry->amount, 4, '.', '') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center text-sm font-black text-blue-600">
                                                {{ $entry->amount > 0 ? '+' : '' }}{{ number_format($entry->amount, 4, '.', '') }}
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Running Balance -->
                                    <td class="py-4 px-4 text-right">
                                        @php
                                            if ($entry->type === 'deduction') {
                                                $prevBalance = $entry->running_balance + $entry->amount;
                                            } else {
                                                $prevBalance = $entry->running_balance - $entry->amount;
                                            }
                                        @endphp
                                        <div class="text-sm font-black text-[#F2A455]">
                                            {{ number_format($entry->running_balance, 4, '.', '') }}
                                        </div>
                                        <div class="text-[10px] font-semibold text-gray-400 tracking-wide mt-0.5">
                                            Prev: {{ number_format($prevBalance, 4, '.', '') }}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-16 text-center">
                                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-3">
                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                        </div>
                                        <span class="block text-sm font-bold text-gray-800">No Transactions Found</span>
                                        <span class="block text-xs font-medium text-gray-500 mt-1">There are no recorded ledger movements for this calendar month.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            </div>

        </div>
    </div>

    <script>
        function forceView(viewType) {
            const listView = document.getElementById('listView');
            const matrixView = document.getElementById('matrixView');
            const btnList = document.getElementById('btnList');
            const btnMatrix = document.getElementById('btnMatrix');

            // Define the styling classes for Active and Inactive states
            const activeClasses = 'px-4 py-2 text-sm font-bold rounded-lg transition-all duration-200 bg-white text-[#df9344] shadow-sm border border-gray-200/50 inline-flex items-center';
            const inactiveClasses = 'px-4 py-2 text-sm font-semibold rounded-lg transition-all duration-200 text-gray-500 hover:text-gray-700 border border-transparent inline-flex items-center';

            if (viewType === 'list') {
                // Show List View, Hide Matrix
                listView.classList.remove('hidden');
                matrixView.classList.add('hidden');
                
                // Style Buttons: List is Active, Matrix is Inactive
                btnList.className = activeClasses;
                btnMatrix.className = inactiveClasses;
            } else {
                // Show Matrix View, Hide List
                matrixView.classList.remove('hidden');
                listView.classList.add('hidden');
                
                // Style Buttons: Matrix is Active, List is Inactive
                btnMatrix.className = activeClasses;
                btnList.className = inactiveClasses;
            }
        }

        function toggleLegend() {
            const legend = document.getElementById('leaveLegend');
            const btnText = document.getElementById('legendBtnText');
            const icon = document.getElementById('legendIcon');
            
            if (legend.classList.contains('max-h-0')) {
                legend.classList.remove('max-h-0', 'opacity-0');
                legend.classList.add('max-h-[500px]', 'opacity-100'); 
                
                btnText.textContent = 'Hide Leave Codes Legend';
                icon.classList.add('-rotate-180'); 
            } else {
                legend.classList.remove('max-h-[500px]', 'opacity-100');
                legend.classList.add('max-h-0', 'opacity-0');
                
                btnText.textContent = 'View Leave Codes Legend';
                icon.classList.remove('-rotate-180'); 
            }
        }

        let activeMatrixCol = null;

        function highlightColumn(index) {
            // Convert index to string to avoid any strict equality issues
            let currentIndex = String(index);

            // 1. If the user clicks the ALREADY ACTIVE column -> turn it OFF and stop
            if (activeMatrixCol === currentIndex) {
                removeHighlights(activeMatrixCol);
                activeMatrixCol = null;
                return; 
            }

            // 2. If clicking a NEW column, clear the old one first
            if (activeMatrixCol !== null) {
                removeHighlights(activeMatrixCol);
            }

            // 3. Apply the highlight to the NEW column
            addHighlights(currentIndex);
            activeMatrixCol = currentIndex;

            // 4. AUTO-OPEN UX: If the legend is closed, automatically slide it open!
            const legend = document.getElementById('leaveLegend');
            if (legend && legend.classList.contains('max-h-0')) {
                toggleLegend(); // Reuses your smooth slide-down function
            }
        }

        function removeHighlights(colIndex) {
            // Remove color from the table column
            document.querySelectorAll('.matrix-col-' + colIndex).forEach(el => {
                el.classList.remove('bg-[#F2A455]/15');
            });

            // Safely remove color from the legend (with strict null checks to prevent crashing)
            const box = document.getElementById('legend-box-' + colIndex);
            const badge = document.getElementById('legend-badge-' + colIndex);
            const text = document.getElementById('legend-text-' + colIndex);
            
            if (box) {
                box.classList.remove('bg-white', 'shadow-sm', 'border-gray-200/60');
                box.classList.add('border-transparent');
            }
            if (badge) {
                badge.classList.remove('bg-[#F2A455]', 'text-white');
                badge.classList.add('bg-[#F2A455]/10', 'text-[#df9344]');
            }
            if (text) {
                text.classList.remove('text-gray-800');
                text.classList.add('text-gray-500');
            }
        }

        function addHighlights(colIndex) {
            // Add color to the table column
            document.querySelectorAll('.matrix-col-' + colIndex).forEach(el => {
                el.classList.add('bg-[#F2A455]/15');
            });

            // Safely add color to the legend
            const box = document.getElementById('legend-box-' + colIndex);
            const badge = document.getElementById('legend-badge-' + colIndex);
            const text = document.getElementById('legend-text-' + colIndex);
            
            if (box) {
                box.classList.add('bg-white', 'shadow-sm', 'border-gray-200/60');
                box.classList.remove('border-transparent');
            }
            if (badge) {
                badge.classList.add('bg-[#F2A455]', 'text-white');
                badge.classList.remove('bg-[#F2A455]/10', 'text-[#df9344]');
            }
            if (text) {
                text.classList.add('text-gray-800');
                text.classList.remove('text-gray-500');
            }
        }
    </script>
</x-app-layout>