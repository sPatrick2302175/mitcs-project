<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Leave Requests Management Masterlist') }}
        </h2>
    </x-slot>

    <style>
        .fc { font-family: inherit; }
        .fc-theme-standard td, .fc-theme-standard th { border-color: #f3f4f6 !important; }
        .fc-scrollgrid { border-radius: 1rem; overflow: hidden; border-color: #f3f4f6 !important; }
        
        /* Brand-themed primary action layout elements for FullCalendar control layer */
        .fc .fc-button-primary { 
            background-color: #1f2937 !important; 
            border-color: transparent !important; 
            text-transform: uppercase; 
            font-weight: 800; 
            font-size: 0.65rem; 
            letter-spacing: 0.05em; 
            padding: 0.5rem 1rem; 
            border-radius: 0.75rem !important; 
            transition: all 0.2s ease; 
        }
        .fc .fc-button-primary:not(:disabled):hover { 
            background-color: #111827 !important; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); 
        }
        .fc .fc-button-primary:not(:disabled):active { transform: scale(0.98); }
        .fc .fc-button-active { background-color: #F2A455 !important; color: white !important; }
        
        .fc .fc-toolbar-title { font-size: 1.25rem !important; font-weight: 900 !important; color: #1f2937; letter-spacing: -0.025em; }
        .fc-col-header-cell-cushion { font-size: 0.7rem; text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em; color: #6b7280; padding: 0.75rem 0 !important; }
        .fc-daygrid-day-number { font-size: 0.875rem; font-weight: 600; color: #374151; padding: 0.5rem !important; }
        
        /* Updated: Brand consistent light backdrop hue highlighting for today's grid index */
        .fc-day-today { background-color: rgba(242, 164, 85, 0.08) !important; }
        
        .fc-event { 
            display: flex !important;
            align-items: center !important;
            padding: 0.35rem 0.625rem !important;
            font-size: 10px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            border-radius: 0.5rem !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03) !important;
            border-width: 1px !important;
            border-style: solid !important;
            cursor: pointer;
            transition: transform 0.15s ease, opacity 0.15s ease;
        }

        .clickable-leave-event { cursor: pointer; }
        .clickable-leave-event:hover { opacity: 0.92; transform: scale(1.01); }

        .status-approved .fc-event-main, 
        .status-pending .fc-event-main{
            display: flex !important;
            align-items: center !important;
            width: 100%;
        }

        /* Approved Style (Green Theme) */
        .status-approved {
            background-color: #ecfdf5 !important;
            color: #059669 !important;
            border-color: rgba(209, 250, 229, 0.7) !important;
        }
        .status-approved, .status-approved .fc-event-main, .status-approved .fc-event-main span {
            color: #059669 !important;
        }
        .status-approved .fc-event-main::before {
            content: '';
            display: inline-block;
            flex-shrink: 0;
            width: 12px; height: 12px;
            margin-right: 5px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' stroke='%23059669' viewBox='0 0 24 24'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 13l4 4L19 7'/%3E%3C/svg%3E");
            background-size: cover;
        }

        /* Pending Style (Amber Theme) */
        .status-pending {
            background-color: #fffbeb !important;
            color: #d97706 !important;
            border-color: rgba(254, 243, 199, 0.7) !important;
        }
        .status-pending, .status-pending .fc-event-main, .status-pending .fc-event-main span {
            color: #d97706 !important;
        }
        .status-pending .fc-event-main::before {
            content: '';
            display: inline-block;
            flex-shrink: 0;
            width: 6px; height: 6px;
            border-radius: 9999px;
            background-color: #f59e0b;
            margin-right: 6px;
            animation: statusPulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes statusPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: .4; transform: scale(1.1); }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="bg-emerald-50/70 backdrop-blur-sm border border-emerald-100 rounded-2xl p-5 shadow-sm transition-all duration-300 animate-fadeIn">
                    <div class="flex items-center">
                        <div class="shrink-0 bg-emerald-100 p-2 rounded-xl">
                            <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <p class="ms-3 text-sm font-bold text-emerald-800">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
            @endif

            {{-- --- 1. SEARCH & FILTER CARD --- --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100/80 p-6">
                <form id="admin-filter-form" method="GET" action="{{ url()->current() }}" onsubmit="event.preventDefault();" class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    
                    {{-- Search Field --}}
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1.5">Search Applications</label>
                        <div class="relative">
                            <input type="text" id="admin-search-input" name="search" value="{{ request('search') }}" placeholder="Search name, or type..." class="w-full bg-gray-50 border border-gray-200/80 text-sm font-medium rounded-xl px-4 py-2.5 focus:bg-white focus:border-[#F2A455] focus:ring-2 focus:ring-[#F2A455]/20 transition-all placeholder-gray-400">
                            
                            <div id="admin-table-spinner" class="hidden absolute right-3 top-3.5">
                                <svg class="animate-spin h-4 w-4 text-[#F2A455]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- Division Selector --}}
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1.5">Filter by Division</label>
                        <select id="admin-division-select" name="division" class="w-full bg-gray-50 border border-gray-200/80 text-sm font-bold text-gray-600 rounded-xl px-4 py-2.5 focus:bg-white focus:border-[#F2A455] focus:ring-2 focus:ring-[#F2A455]/20 transition-all">
                            <option value="">All Divisions</option>
                            @if(isset($divisions) && $divisions->count() > 0)
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}" {{ request('division') == $division->id ? 'selected' : '' }}>
                                        {{ $division->division_name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    {{-- Status Dropdown --}}
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1.5">Review Status State</label>
                        <select id="admin-status-select" name="status" class="w-full bg-gray-50 border border-gray-200/80 text-sm font-bold text-gray-600 rounded-xl px-4 py-2.5 focus:bg-white focus:border-[#F2A455] focus:ring-2 focus:ring-[#F2A455]/20 transition-all">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending (Needs Review)</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="disapproved" {{ request('status') == 'disapproved' ? 'selected' : '' }}>Disapproved</option>
                        </select>
                    </div>
                </form>
            </div>

            {{-- --- 2. CALENDAR SCHEDULE SECTION --- --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="mb-6 flex flex-col lg:flex-row justify-between items-start lg:items-center pb-5 border-b border-gray-100/80 gap-4">
                    <div>
                        <h3 class="text-lg font-extrabold text-gray-800 tracking-tight">Global Leave Schedule</h3>
                        <p class="text-xs text-gray-400 font-semibold mt-0.5">Overview of all employee leaves across divisions.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 w-full lg:w-auto justify-between lg:justify-end">
                        <a href="{{ route('admin.custom-holidays.index') }}" 
                        class="inline-flex items-center px-4 py-2.5 bg-gray-900 hover:bg-gray-800 text-white text-xs font-black uppercase tracking-wider rounded-xl transition-all duration-200 shadow-md shadow-gray-900/10 active:scale-[0.98]">
                            <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Manage Corporate Holidays
                        </a>

                        <div class="flex items-center bg-gray-50/80 pl-4 pr-3 py-1.5 rounded-xl border border-gray-200/60 shadow-sm shrink-0 transition-all">
                            <span id="custom-year-display" class="text-2xl font-black text-gray-800 tracking-tight min-w-[4.5rem] text-center select-none">
                                {{ date('Y') }}
                            </span>
                            
                            <div class="flex flex-col ml-2.5 border-l border-gray-200/80 pl-2.5 text-[#F2A455]">
                                <button id="year-btn-up" type="button" class="hover:text-[#df9344] p-0.5 focus:outline-none transition-colors transform active:scale-90" title="Next Year">
                                    <svg class="w-4 h-4 font-bold" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5"></path>
                                    </svg>
                                </button>
                                <button id="year-btn-down" type="button" class="hover:text-[#df9344] p-0.5 focus:outline-none transition-colors transform active:scale-90 mt-0.5" title="Previous Year">
                                    <svg class="w-4 h-4 font-bold" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="admin-calendar" class="min-h-[500px]"></div>

                <div class="mt-5 flex flex-wrap gap-x-5 gap-y-2.5 items-center text-[10px] font-black text-gray-400 uppercase tracking-wider bg-gray-50/60 border border-gray-100 p-3 rounded-xl shadow-inner">
                    <span class="text-gray-500">Schedule Legend:</span>
                    <span class="inline-flex items-center gap-1.5 text-emerald-700"><span class="w-3 h-3 rounded bg-emerald-50 border border-emerald-200"></span> Approved Leaves</span>
                    <span class="inline-flex items-center gap-1.5 text-amber-700"><span class="w-3 h-3 rounded bg-amber-50 border border-amber-200"></span> Pending Requests</span>
                    <span class="inline-flex items-center gap-1.5 text-gray-600"><span class="w-3 h-3 rounded bg-orange-500"></span> None Regular Holidays</span>
                    <span class="inline-flex items-center gap-1.5 text-blue-600"><span class="w-3 h-3 rounded bg-blue-500"></span> Regular Holidays</span>
                </div>
            </div>

            <div id="calendar-data-store" class="hidden" data-events="{{ json_encode($calendarEvents ?? []) }}"></div>

            {{-- --- 3. DATATABLE RECORD LIST --- --}}
            <div id="admin-table-container" class="bg-white rounded-2xl shadow-xl shadow-gray-200/40 border border-gray-100/80 overflow-hidden transition-all duration-300">
                <div class="p-6 md:p-8 border-b border-gray-100/60 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white">
                    <div>
                        <h3 class="text-xl font-extrabold text-gray-800 tracking-tight">Civil Service Form No. 6 Applications</h3>
                        <p class="text-sm text-gray-400 font-semibold mt-1">Review, certify, and log final action outcomes for corporate leave allocations.</p>
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
                                <tr class="hover:bg-gray-50/40 transition-colors duration-200 group">
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
                                            <span class="inline-flex items-center px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg bg-amber-50 text-amber-600 border border-amber-100/60 shadow-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5 animate-pulse"></span>
                                                Pending
                                            </span>
                                        @elseif($request->status === 'approved')
                                            <span class="inline-flex items-center px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100/60 shadow-sm">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Approved
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg bg-rose-50 text-rose-600 border border-rose-100/60 shadow-sm">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
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

                @if(method_exists($leaveRequests, 'links') && $leaveRequests->hasPages())
                    <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100/60">
                        {{ $leaveRequests->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let calendar;

            // --- 1. FULLCALENDAR INITIALIZATION WITH INFINITE YEAR CONSTRAINTS ---
            const calendarEl = document.getElementById('admin-calendar');
            const yearDisplay = document.getElementById('custom-year-display');
            
            // Define as a let variable so the AJAX filter pipeline can update records seamlessly
            let calendarEvents = @json($calendarEvents ?? []); 

            if (calendarEl) {
                calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    height: 'auto',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,dayGridWeek'
                    },
                    buttonText: { today: 'Today', month: 'Month', week: 'Week' },
                    
                    // 🌟 REMOVE YEAR FROM CENTER HEADER
                    titleFormat: { month: 'long' },

                    // 🌟 AUTO-SYNCHRONIZE THE TOP RIGHT YEAR PANEL ON PREV/NEXT MONTH CLICK
                    datesSet: function(info) {
                        let activeViewDate = calendar.getDate();
                        let currentYearContext = activeViewDate.getFullYear();
                        if (yearDisplay && yearDisplay.innerText != currentYearContext) {
                            yearDisplay.innerText = currentYearContext;
                        }
                    },

                    // 🌟 DYNAMIC MATHEMATICAL RECURRING HOLIDAY PROPAGATOR
                    events: function(fetchInfo, successCallback, failureCallback) {
                        let dynamicEvents = [];
                        let startYear = fetchInfo.start.getFullYear();
                        let endYear = fetchInfo.end.getFullYear();

                        calendarEvents.forEach(event => {
                            const isRegular = event.is_regular || (event.extendedProps && event.extendedProps.is_regular);

                            if (isRegular) {
                                // Clone the item across every future/past iteration window visible in this frame
                                for (let y = startYear; y <= endYear; y++) {
                                    let clonedEvent = { ...event }; 
                                    let monthDayStr = String(clonedEvent.start).substring(5, 10); 
                                    clonedEvent.start = `${y}-${monthDayStr}`;
                                    
                                    if (clonedEvent.end) {
                                        let endMonthDayStr = String(clonedEvent.end).substring(5, 10);
                                        clonedEvent.end = `${y}-${endMonthDayStr}`;
                                    }
                                    
                                    clonedEvent.id = `${event.id || 'holiday'}-infinite-${y}`; 
                                    dynamicEvents.push(clonedEvent);
                                }
                            } else {
                                // Keep standard single-instance leave records untouched
                                dynamicEvents.push(event);
                            }
                        });

                        successCallback(dynamicEvents);
                    },

                    eventDidMount: function(info) {
                        if (info.event.extendedProps && info.event.extendedProps.leave_id) {
                            info.el.classList.add('clickable-leave-event');
                        }
                    },
                    eventClick: function(info) {
                        if (info.event.url) { return; }
                        const props = info.event.extendedProps;
                        
                        if (props && props.leave_id) {
                            const leaveRequestId = props.leave_id; // Read directly from safe extended properties
                            const reviewRouteTemplate = "{{ route('admin.leave-requests.review', ':id') }}";
                            window.location.href = reviewRouteTemplate.replace(':id', leaveRequestId);
                            info.jsEvent.preventDefault();
                        }
                    },
                    eventContent: function(arg) {
                        const isLeaveRequest = arg.event.extendedProps && arg.event.extendedProps.leave_id;
                        const textColorStyle = isLeaveRequest ? '' : `color: ${arg.event.textColor || '#ffffff'};`;

                        return {
                            html: `
                                <div style="display: flex; align-items: center; width: 100%; overflow: hidden; padding: 0 4px;">
                                    <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; ${textColorStyle}">${arg.event.title}</span>
                                </div>
                            `
                        };
                    }
                });
                calendar.render();

                // 🌟 TELEPORT EVENTS VIA TIMESHIFT BUTTON CLICK BINDINGS
                document.getElementById('year-btn-up').addEventListener('click', function() {
                    let currentDate = calendar.getDate();
                    let nextYear = currentDate.getFullYear() + 1;
                    let currentMonth = String(currentDate.getMonth() + 1).padStart(2, '0');
                    calendar.gotoDate(`${nextYear}-${currentMonth}-01`);
                });

                document.getElementById('year-btn-down').addEventListener('click', function() {
                    let currentDate = calendar.getDate();
                    let prevYear = currentDate.getFullYear() - 1;
                    let currentMonth = String(currentDate.getMonth() + 1).padStart(2, '0');
                    calendar.gotoDate(`${prevYear}-${currentMonth}-01`);
                });
            }

            // --- 2. DYNAMIC SEARCH ENGINE WITH AJAX PAGINATION DELEGATION ---
            const form = document.getElementById('admin-filter-form');
            const searchInput = document.getElementById('admin-search-input');
            const statusSelect = document.getElementById('admin-status-select');
            const divisionSelect = document.getElementById('admin-division-select');
            const tableContainer = document.getElementById('admin-table-container');
            const spinner = document.getElementById('admin-table-spinner');

            let debounceTimeout;

            function fetchFilteredData(targetUrl = null) {
                spinner.classList.remove('hidden');
                tableContainer.classList.add('opacity-60');

                let fetchUrl = targetUrl;
                
                if (!fetchUrl) {
                    const formData = new FormData(form);
                    const queryParams = new URLSearchParams(formData).toString();
                    fetchUrl = `${form.action}?${queryParams}`;
                }

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
                    
                    // 🌟 AJAX FILTER RE-BINDING COMPATIBILITY
                    const freshDataStore = freshDocument.getElementById('calendar-data-store');
                    if (freshDataStore && calendar) {
                        // Update the reference data scope array variable
                        calendarEvents = JSON.parse(freshDataStore.dataset.events);
                        // Trigger full engine computation loop redraw safely
                        calendar.refetchEvents();
                    }
                    
                    window.history.pushState({}, '', fetchUrl);
                })
                .catch(error => console.error('Error handling filtering:', error))
                .finally(() => {
                    spinner.classList.add('hidden');
                    tableContainer.classList.remove('opacity-60');
                });
            }

            tableContainer.addEventListener('click', function(event) {
                const linkElement = event.target.closest('nav a, .pagination a');
                if (linkElement && linkElement.getAttribute('href')) {
                    event.preventDefault();
                    fetchFilteredData(linkElement.getAttribute('href'));
                }
            });

            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimeout);
                debounceTimeout = setTimeout(() => fetchFilteredData(), 300);
            });

            statusSelect.addEventListener('change', () => fetchFilteredData());
            if (divisionSelect) {
                divisionSelect.addEventListener('change', () => fetchFilteredData());
            }
        });
    </script>
</x-app-layout>