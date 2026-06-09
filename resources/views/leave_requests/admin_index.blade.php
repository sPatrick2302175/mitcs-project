<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Leave Requests Management Masterlist') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="bg-emerald-50/70 backdrop-blur-sm border border-emerald-100 rounded-xl p-5 shadow-sm transition-all duration-300 animate-fadeIn">
                    <div class="flex items-center">
                        <div class="shrink-0 bg-emerald-100 p-2 rounded-lg">
                            <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <p class="ms-3 text-sm font-bold text-emerald-800">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
            @endif

            <!--  NEW: Dynamic Search & Filter Controls Panel -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <!-- Using a default dynamic fallback action to current window URL route -->
                <form id="admin-filter-form" method="GET" action="{{ url()->current() }}" onsubmit="event.preventDefault();" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    
                    <!-- Unified Search bar (Handles Name, ID, or Leave Type) -->
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Search Applications</label>
                        <div class="relative">
                            <input type="text" id="admin-search-input" name="search" value="{{ request('search') }}" placeholder="Search employee name, ID number, or type of leave..." class="w-full bg-gray-50 border border-gray-200 text-sm rounded-xl px-4 py-2 focus:bg-white focus:border-indigo-400 focus:ring-0 transition-colors">
                            
                            <!-- Search Field Loading Spinner -->
                            <div id="admin-table-spinner" class="hidden absolute right-3 top-2.5">
                                <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Status Filter Dropdown -->
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Review Status State</label>
                        <select id="admin-status-select" name="status" class="w-full bg-gray-50 border border-gray-200 text-sm rounded-xl px-4 py-2 focus:bg-white focus:border-indigo-400 focus:ring-0 transition-colors">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending (Needs Review)</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="disapproved" {{ request('status') == 'disapproved' ? 'selected' : '' }}>Disapproved</option>
                        </select>
                    </div>
                </form>
            </div>

            <!-- Dynamic Table Container Block Wrapper -->
            <div id="admin-table-container" class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 overflow-hidden transition-all duration-300">
                <div class="p-6 md:p-8 border-b border-gray-100/60 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white">
                    <div>
                        <h3 class="text-xl font-extrabold text-gray-800 tracking-tight">Civil Service Form No. 6 Applications</h3>
                        <p class="text-sm text-gray-500 font-medium mt-1">Review, certify, and log final action outcomes for corporate leave allocations.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/80 border-b border-gray-100">
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Employee Metadata</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Leave Parameters</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Inclusive Range</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400 text-center">Days</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400 text-center">Status</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400 text-right">Processing Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50/80">
                            @forelse($leaveRequests as $request)
                                <tr class="hover:bg-gray-50/50 transition-colors duration-200 group">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-800 group-hover:text-gray-900 transition-colors">
                                            {{ $request->employee->first_name ?? '' }} {{ $request->employee->last_name ?? 'Unknown Employee' }}
                                        </div>
                                        <div class="text-xs text-gray-500 font-medium mt-0.5">
                                            ID: <span class="bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded text-[10px] font-bold">{{ $request->employee->employee_id_number ?? 'N/A' }}</span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-bold text-gray-700">{{ $request->leave_type }}</span>
                                        @if($request->leave_type === 'Others' && $request->leave_type_others)
                                            <span class="block text-[11px] font-bold text-[#F2A455] uppercase tracking-wider mt-0.5">({{ $request->leave_type_others }})</span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-800">
                                            {{ \Carbon\Carbon::parse($request->start_date)->format('M d, Y') }}
                                        </div>
                                        <div class="text-xs text-gray-400 font-medium mt-0.5">
                                            to {{ \Carbon\Carbon::parse($request->end_date)->format('M d, Y') }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-extrabold text-gray-800">
                                        {{ number_format($request->working_days_applied, 1) }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($request->status === 'pending')
                                            <span class="inline-flex px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg bg-amber-50 text-amber-600 border border-amber-100/60 shadow-sm">
                                                Pending
                                            </span>
                                        @elseif($request->status === 'approved')
                                            <span class="inline-flex px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100/60 shadow-sm">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Approved
                                            </span>
                                        @else
                                            <span class="inline-flex px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg bg-rose-50 text-rose-600 border border-rose-100/60 shadow-sm">
                                                Disapproved
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                        <a href="{{ route('admin.leave-requests.review', $request->id) }}" 
                                            class="inline-flex items-center px-4 py-2 {{ $request->status === 'pending' ? 'bg-[#F2A455] hover:bg-[#df9344] text-white shadow-md shadow-orange-500/10' : 'bg-white hover:bg-gray-50 text-gray-600 border border-gray-200/60 shadow-sm' }} text-xs font-bold rounded-xl transition-all duration-200 active:scale-[0.98]">
                                            {{ $request->status === 'pending' ? 'Review & Action' : 'View Record' }}
                                        </a>

                                        @if($request->status === 'approved')
                                            <a href="{{ route('leave-requests.pdf', $request->id) }}" class="inline-flex items-center justify-center px-3 py-1.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 hover:text-indigo-800 font-bold text-[10px] uppercase tracking-wider rounded-lg border border-indigo-100/60 transition-colors">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                View PDF
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-16 text-center">
                                        <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-gray-100">
                                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        </div>
                                        <h3 class="text-base font-bold text-gray-800 mb-1">No applications logged</h3>
                                        <p class="text-sm text-gray-500 font-medium">There are currently no active or historic leave requests matching your filters.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Links Wrapper (Kept safe inside dynamic zone) -->
                @if(method_exists($leaveRequests, 'links') && $leaveRequests->hasPages())
                    <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100/60">
                        {{ $leaveRequests->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ⚙️ JavaScript Engine Block -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('admin-filter-form');
            const searchInput = document.getElementById('admin-search-input');
            const statusSelect = document.getElementById('admin-status-select');
            const tableContainer = document.getElementById('admin-table-container');
            const spinner = document.getElementById('admin-table-spinner');

            let debounceTimeout;

            function fetchFilteredData() {
                // Activate processing visibility layers
                spinner.classList.remove('hidden');
                tableContainer.classList.add('opacity-60');

                const formData = new FormData(form);
                const queryParams = new URLSearchParams(formData).toString();
                const fetchUrl = `${form.action}?${queryParams}`;

                fetch(fetchUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.text())
                .then(htmlString => {
                    const parser = new DOMParser();
                    const freshDocument = parser.parseFromString(htmlString, 'text/html');
                    const freshTableContent = freshDocument.getElementById('admin-table-container');

                    if (freshTableContent) {
                        tableContainer.innerHTML = freshTableContent.innerHTML;
                    }
                    
                    // Sync user navigation state parameter paths safely
                    window.history.pushState({}, '', fetchUrl);
                })
                .catch(error => console.error('Error handling administrative masterlist dataset filtration request:', error))
                .finally(() => {
                    // Normalize active visibility layouts
                    spinner.classList.add('hidden');
                    tableContainer.classList.remove('opacity-60');
                });
            }

            // 300ms Debounce setup to capture input cycles gently
            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimeout);
                debounceTimeout = setTimeout(fetchFilteredData, 300);
            });

            // Trigger instantly on drop-down update actions
            statusSelect.addEventListener('change', fetchFilteredData);
        });
    </script>
</x-app-layout>