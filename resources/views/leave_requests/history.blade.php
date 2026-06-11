<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('My Leave History') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <form id="filter-form" method="GET" action="{{ route('leave-requests.history') }}" onsubmit="event.preventDefault();" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Search Keywords</label>
                        <div class="relative">
                            <input type="text" id="search-input" name="search" value="{{ request('search') }}" placeholder="Type leave type, detail, or status..." class="w-full bg-gray-50 border border-gray-200 text-sm rounded-xl px-4 py-2 focus:bg-white focus:border-indigo-400 focus:ring-0 transition-colors">
                            <div id="table-spinner" class="hidden absolute right-3 top-2.5">
                                <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Status</label>
                        <select id="status-select" name="status" class="w-full bg-gray-50 border border-gray-200 text-sm rounded-xl px-4 py-2 focus:bg-white focus:border-indigo-400 focus:ring-0 transition-colors">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="disapproved" {{ request('status') == 'disapproved' ? 'selected' : '' }}>Disapproved</option>
                        </select>
                    </div>
                </form>
            </div>

            <div id="history-table-container" class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 overflow-hidden transition-all duration-200">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100/60">
                                <th class="py-4 px-6 text-[10px] font-bold uppercase tracking-wider text-gray-400">Date Filed</th>
                                <th class="py-4 px-6 text-[10px] font-bold uppercase tracking-wider text-gray-400">Leave Type</th>
                                <th class="py-4 px-6 text-[10px] font-bold uppercase tracking-wider text-gray-400">Inclusive Dates</th>
                                <th class="py-4 px-6 text-[10px] font-bold uppercase tracking-wider text-gray-400 text-center">Days</th>
                                <th class="py-4 px-6 text-[10px] font-bold uppercase tracking-wider text-gray-400">Status</th>
                                <th class="py-4 px-6 text-[10px] font-bold uppercase tracking-wider text-gray-400 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($leaveRequests as $request)
                                <tr class="hover:bg-gray-50/30 transition-colors duration-200">
                                    <td class="py-4 px-6 text-sm font-semibold text-gray-800 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($request->date_of_filing)->format('M d, Y') }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="text-sm font-bold text-gray-700 block">{{ $request->leave_type }}</span>
                                        @if($request->leave_type_others)
                                            <span class="text-[11px] text-gray-400 block italic">Specifics: {{ $request->leave_type_others }}</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-sm font-medium text-gray-600 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($request->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($request->end_date)->format('M d, Y') }}
                                    </td>
                                    <td class="py-4 px-6 text-sm font-black text-gray-800 text-center">
                                        {{ $request->working_days_applied }}
                                    </td>
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        @if($request->status === 'pending')
                                            <span class="inline-flex items-center px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg bg-amber-50 text-amber-600 border border-amber-100/60 shadow-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5 animate-pulse"></span>
                                                Pending
                                            </span>
                                        @elseif($request->status === 'approved')
                                            <span class="inline-flex items-center px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100/60 shadow-sm">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Approved
                                            </span>
                                        @elseif($request->status === 'disapproved')
                                            <span class="inline-flex items-center px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg bg-rose-50 text-rose-600 border border-rose-100/60 shadow-sm">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                Disapproved
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg bg-blue-50 text-blue-600 border border-blue-100/60 shadow-sm">In Review</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-center whitespace-nowrap">
                                        @if($request->status !== 'pending')
                                            <a href="{{ route('leave-requests.pdf', $request->id) }}" class="inline-flex items-center justify-center px-3 py-1.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 hover:text-indigo-800 font-bold text-[10px] uppercase tracking-wider rounded-lg border border-indigo-100/60 transition-colors">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                View PDF
                                            </a>
                                        @else
                                            <span class="text-gray-400 text-[10px] font-bold uppercase tracking-wider bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100/80">Locked</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center">
                                        <span class="text-sm font-medium text-gray-500">No history matches your filters.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($leaveRequests->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100/60 bg-gray-50/50">
                        {{ $leaveRequests->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('filter-form');
            const searchInput = document.getElementById('search-input');
            const statusSelect = document.getElementById('status-select'); // Guarded against crash
            const tableContainer = document.getElementById('history-table-container');
            const spinner = document.getElementById('table-spinner');

            let debounceTimeout;

            function fetchFilteredData() {
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
                    const freshTableContent = freshDocument.getElementById('history-table-container');

                    if (freshTableContent) {
                        tableContainer.innerHTML = freshTableContent.innerHTML;
                    }

                    window.history.pushState({}, '', fetchUrl);
                })
                .catch(error => console.error('Error fetching filtered dataset:', error))
                .finally(() => {
                    spinner.classList.add('hidden');
                    tableContainer.classList.remove('opacity-60');
                });
            }

            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimeout);
                debounceTimeout = setTimeout(fetchFilteredData, 300);
            });

            // Only statusSelect remains here now! No dead references.
            statusSelect.addEventListener('change', fetchFilteredData);
        });
    </script>
</x-app-layout>