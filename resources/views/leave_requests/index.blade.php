<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Dashboard') }}
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

        /* Updated Base Event Style */
        .fc-event {
            display: flex !important;
            align-items: center !important;
            padding: 0.25rem 0.625rem !important;
            font-size: 10px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            border-radius: 0.5rem !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
            border-width: 1px !important;
            border-style: solid !important;
            /*cursor: pointer;*/
        }

        .fc-event .fc-event-main {
            display: flex;
            align-items: center;
            width: 100%;
            color: inherit;
        }

        /* PENDING STATUS */
        .status-pending {
            background-color: #fffbeb !important;
            color: #d97706 !important;
            border-color: rgba(254, 243, 199, 0.6) !important;
        }
        .status-pending .fc-event-main::before {
            content: '';
            display: inline-block;
            flex-shrink: 0;
            width: 6px; height: 6px;
            border-radius: 9999px;
            background-color: #f59e0b;
            margin-right: 6px;
            animation: event-pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* APPROVED STATUS */
        .status-approved {
            background-color: #ecfdf5 !important;
            color: #059669 !important;
            border-color: rgba(209, 250, 229, 0.6) !important;
        }
        .status-approved .fc-event-main::before {
            content: '';
            display: inline-block;
            flex-shrink: 0;
            width: 12px; height: 12px;
            margin-right: 4px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' stroke='%23059669' viewBox='0 0 24 24'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 13l4 4L19 7'/%3E%3C/svg%3E");
            background-size: cover;
        }

    
        /* Pulsing Animation */
        @keyframes event-pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }

        /* -------------------------------------
        CO-WORKER: APPROVED
        -------------------------------------- */
        .status-coworker-approved {
            background-color: #ffffff !important; /* bg-white */
            color: #94a3b8 !important; /* text-slate-400 */
            border-color: #cbd5e1 !important; /* border-slate-300 */
            border-style: dashed !important; /* Dashed border for unconfirmed */
        }
        
        /* Gray Checkmark SVG */
        .status-coworker-approved .fc-event-main::before {
            content: '';
            display: inline-block;
            flex-shrink: 0;
            width: 12px; height: 12px;
            margin-right: 4px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' stroke='%2364748b' viewBox='0 0 24 24'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 13l4 4L19 7'/%3E%3C/svg%3E");
            background-size: cover;
        }

        /* -------------------------------------
        CO-WORKER: PENDING
        -------------------------------------- */
        .status-coworker-pending {
            background-color: #ffffff !important; /* bg-white */
            color: #94a3b8 !important; /* text-slate-400 */
            border-color: #cbd5e1 !important; /* border-slate-300 */
            border-style: dashed !important; /* Dashed border for unconfirmed */
        }
        
        /* Hollow Gray Circle (Static) */
        .status-coworker-pending .fc-event-main::before {
            content: '';
            display: inline-block;
            flex-shrink: 0;
            width: 8px; height: 8px;
            border-radius: 9999px;
            border: 2px solid #cbd5e1; /* border-slate-300 */
            margin-right: 6px;
            background-color: transparent;
        }

        /* -------------------------------------
        CO-WORKER: DISAPPROVED (Fallback)
        -------------------------------------- */
        .status-coworker-disapproved {
            background-color: #f8fafc !important; 
            color: #94a3b8 !important; 
            border-color: #e2e8f0 !important; 
        }
        
        /* Gray X SVG */
        .status-coworker-disapproved .fc-event-main::before {
            content: '';
            display: inline-block;
            flex-shrink: 0;
            width: 12px; height: 12px;
            margin-right: 4px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' stroke='%2394a3b8' viewBox='0 0 24 24'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 18L18 6M6 6l12 12'/%3E%3C/svg%3E");
            background-size: cover;
        }
    </style>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if(session('success'))
                <div class="bg-emerald-50/70 backdrop-blur-sm border border-emerald-100 rounded-2xl p-5 shadow-sm transition-all duration-300 animate-fadeIn flex items-start">
                    <div class="shrink-0 bg-emerald-100 p-2 rounded-xl">
                        <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="ms-4 mt-0.5">
                        <h3 class="text-sm font-extrabold text-emerald-800 tracking-tight">Success</h3>
                        <p class="text-sm font-medium text-emerald-700 mt-0.5">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if($employee)
                @php
                    // 1. Define the 4 main leaves we want to display
                    $displayCodes = ['VL', 'SL', 'FL', 'SPL'];
                    
                    // 2. Fetch ONLY those four leaves and sort them exactly as defined above
                    $filteredLeaves = \App\Models\LeaveType::whereIn('code', $displayCodes)
                        ->get()
                        ->sortBy(function($model) use ($displayCodes) {
                            return array_search($model->code, $displayCodes);
                        });
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @forelse($filteredLeaves as $index => $leaveType)
                        @php
                            // Fetch balance from the preloaded relationship row securely
                            $balanceRecord = $employee->leaveBalances->firstWhere('leave_type_id', $leaveType->id);
                            $balanceValue = $balanceRecord ? (float)$balanceRecord->balance : 0.00;
                            
                            // Safely cycle through your original color highlights
                            $glowClasses = [
                                'bg-indigo-50', 
                                'bg-emerald-50', 
                                'bg-amber-50', 
                                'bg-purple-50'
                            ];
                            $currentGlow = $glowClasses[$index % count($glowClasses)];
                            
                            // Clean up the name for a gorgeous UI (e.g. "Vacation" instead of "Vacation Leave")
                            $leaveName = $leaveType->leave_type_name ?? $leaveType->name;
                        @endphp

                        <div class="bg-white border border-gray-100/60 rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 relative overflow-hidden group">
                            <div class="absolute -right-4 -top-4 w-24 h-24 {{ $currentGlow }} rounded-full blur-2xl opacity-50 group-hover:opacity-100 transition-opacity duration-500"></div>
                            <div class="relative z-10">
                                <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400 block mb-1">
                                    {{ str_replace(' Leave', '', $leaveName) }} Leave
                                </span>
                                
                                <div class="flex flex-col justify-end">
                                    <div class="flex items-baseline space-x-1.5">
                                        @if($balanceValue < 0)
                                            <span class="text-3xl font-black text-gray-800">0.00</span>
                                        @else
                                            <span class="text-3xl font-black text-gray-800">{{ number_format($balanceValue, 2) }}</span>
                                        @endif
                                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Days</span>
                                    </div>

                                    @if($balanceValue < 0)
                                        <span class="text-[10px] font-bold text-rose-500 uppercase tracking-wider flex items-center mt-1.5 animate-fade-in">
                                            <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Advance Taken: {{ number_format(abs($balanceValue), 2) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                    @empty
                        <div class="col-span-full bg-white border border-gray-100/60 rounded-2xl p-6 text-center shadow-sm">
                            <span class="text-sm font-bold text-gray-400">No core leave types configured.</span>
                        </div>
                    @endforelse
                </div>
                <div class="flex justify-end items-center mb-4">
                    <a href="{{ route('leave-ledger.index') }}" class="group inline-flex items-center text-xs font-bold text-gray-500 hover:text-[#F2A455] uppercase tracking-wider transition-colors duration-200">
                        Balance Record
                        <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
                @php
                    $today = \Carbon\Carbon::now()->startOfDay();
                    
                    // 1. FILTER: Keep only leaves where the end_date is today or in the future
                    // 2. SORT: Arrange them by start_date ascending (closest to today at the top)
                    $upcomingRequests = $leaveRequests->filter(function($request) use ($today) {
                        $endDate = \Carbon\Carbon::parse($request->end_date)->startOfDay();
                        return $endDate->greaterThanOrEqualTo($today);
                    })->sortBy(function($request) {
                        return \Carbon\Carbon::parse($request->start_date)->timestamp;
                    });
                @endphp

                <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 overflow-hidden transition-all duration-300 mt-8">
                    <div class="p-6 md:p-8 border-b border-gray-100/60 flex flex-col md:flex-row justify-between items-center gap-4 bg-white">
                        <h3 class="text-xl font-black text-gray-800 tracking-tight">Upcoming Leave Applications</h3>
                        <a href="{{ route('leave-requests.create') }}" class="inline-flex items-center px-5 py-2.5 bg-[#F2A455] hover:bg-[#df9344] text-white text-xs font-extrabold uppercase tracking-wider rounded-xl shadow-md shadow-orange-500/20 transition-all duration-200 active:scale-[0.98]">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Apply for Leave
                        </a>
                    </div>

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
                                {{-- Loop through the strictly filtered UPCOMING requests --}}
                                @forelse($upcomingRequests as $request)
                                    <tr class="hover:bg-gray-50/30 transition-colors duration-200">
                                        <td class="py-4 px-6 text-sm font-semibold text-gray-800 whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($request->date_of_filing)->format('M d, Y') }}
                                        </td>
                                        <td class="py-4 px-6">
                                            <span class="text-sm font-bold text-gray-700 block">
                                                {{ $request->leaveType->leave_type_name ?? 'Standard Leave' }}
                                            </span>
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
                                            @endif
                                        </td>
                                        <td class="py-4 px-6 text-center whitespace-nowrap space-x-1.5">
                                            <a href="{{ route('leave-requests.show', $request->id) }}" class="inline-flex items-center justify-center px-3 py-1.5 bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-800 font-bold text-[10px] uppercase tracking-wider rounded-lg border border-gray-200/60 shadow-sm transition-all duration-200 active:scale-[0.98]">
                                                View Record
                                            </a>

                                            @if($request->status === 'approved')
                                                <a href="{{ route('leave-requests.pdf', $request->id) }}" class="inline-flex items-center justify-center px-3 py-1.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 hover:text-indigo-800 font-bold text-[10px] uppercase tracking-wider rounded-lg border border-indigo-100/60 transition-colors shadow-sm">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                    View PDF
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center">
                                            <div class="flex flex-col items-center justify-center space-y-3">
                                                <div class="bg-gray-50 p-3 rounded-full">
                                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                </div>
                                                <span class="text-sm font-medium text-gray-500">No upcoming leave applications found.</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table> 
                    </div>
                </div>
            @else
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 shadow-sm flex items-start space-x-4">
                    <div class="shrink-0 bg-amber-100 p-2.5 rounded-xl text-amber-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-amber-900 tracking-tight">Employee Profile Mapping Missing</h4>
                        <p class="text-sm font-medium text-amber-700 mt-1">
                            Your account is fully authenticated, but it has not been linked to an employee profile yet. Personal leave balances and action options will remain hidden until your system administrator creates your matching Employee entry.
                        </p>
                    </div>
                </div>
            @endif

            <!--Calendar-->

            <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 overflow-hidden p-6 md:p-8">
                
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-5 mb-6 border-b border-gray-100/80 gap-4">
                    <div>
                        <h3 class="text-xl font-black text-gray-800 tracking-tight">NGC Calendar & Holidays</h3>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mt-1">Global view of scheduled events and corporate leaves</p>
                    </div>

                    <div class="flex items-center bg-gray-50/80 pl-4 pr-3 py-1.5 rounded-xl border border-gray-200/60 shadow-sm shrink-0 self-start sm:self-center transition-all">
                        <span id="custom-year-display" class="text-2xl font-black text-gray-800 tracking-tight min-w-[4.5rem] text-center select-none">
                            {{ date('Y') }}
                        </span>
                        
                        <div class="flex flex-col ml-2.5 border-l border-gray-200/80 pl-2.5 text-[#F2A455]">
                            <button id="year-btn-up" class="hover:text-[#df9344] p-0.5 focus:outline-none transition-colors transform active:scale-90" title="Next Year">
                                <svg class="w-4 h-4 font-bold" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5"></path>
                                </svg>
                            </button>
                            <button id="year-btn-down" class="hover:text-[#df9344] p-0.5 focus:outline-none transition-colors transform active:scale-90 mt-0.5" title="Previous Year">
                                <svg class="w-4 h-4 font-bold" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                    <div id="calendar" class="min-h-[500px]"></div>
                    
                    <div class="mt-5 flex flex-wrap gap-x-5 gap-y-2.5 items-center text-[10px] font-black text-gray-400 uppercase tracking-wider bg-gray-50/60 border border-gray-100 p-3 rounded-xl shadow-inner">
                        <span class="text-gray-500">Schedule Legend:</span>
                        <span class="inline-flex items-center gap-1.5 text-emerald-700"><span class="w-3 h-3 rounded bg-emerald-50 border border-emerald-200"></span> Approved Leaves</span>
                        <span class="inline-flex items-center gap-1.5 text-amber-700"><span class="w-3 h-3 rounded bg-amber-50 border border-amber-200"></span> Pending Requests</span>
                        <span class="inline-flex items-center gap-1.5 text-blue-600"><span class="w-3 h-3 rounded bg-blue-500"></span> Regular Holidays</span>
                        <span class="inline-flex items-center gap-1.5 text-gray-600"><span class="w-3 h-3 rounded bg-orange-500"></span> Non-Regular Holidays</span>
                    </div>
                </div>

            </div>
    </div>

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // 1. Get baseline records securely loaded from controller array mapping pipelines
            const baseEvents = @json($calendarEvents ?? []);
            console.log('Sanitized Base Event Models:', baseEvents);

            const yearDisplay = document.getElementById('custom-year-display');
            var calendarEl = document.getElementById('calendar');
            
            // 2. Initialize Calendar Engine Configuration Layer
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 'auto',
                contentHeight: 'auto',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,dayGridWeek'
                },
                buttonText: {
                    today: 'Today',
                    month: 'Month',
                    week: 'Week'
                },
                
                // 🌟 REMOVE YEAR FROM CENTER DISPLAY TITLE (Shows only the Month Name now)
                titleFormat: { month: 'long' },

                // 🌟 DYNAMIC DATESYNCHRONIZER FOR MANUAL PREV/NEXT CLICKS
                datesSet: function(info) {
                    let activeViewDate = calendar.getDate();
                    let currentYearContext = activeViewDate.getFullYear();
                    
                    if (yearDisplay.innerText != currentYearContext) {
                        yearDisplay.innerText = currentYearContext;
                    }
                },
                
                // 🌟 TRUE MATHEMATICAL INFINITE EVENT GENERATOR HOOK
                events: function(fetchInfo, successCallback, failureCallback) {
                    let dynamicEvents = [];
                    let startYear = fetchInfo.start.getFullYear();
                    let endYear = fetchInfo.end.getFullYear();

                    baseEvents.forEach(event => {
                        
                        const isActive = event.is_active ?? (event.extendedProps && event.extendedProps.is_active) ?? true;
                            
                        // If the admin toggled it off (false or 0), skip rendering it entirely!
                        if (isActive === false || isActive === 0 || isActive === "0") {
                            return; // Skips to the next event
                        }

                        // Check base properties or nested extended metadata safely
                        const isRegular = event.is_regular || (event.extendedProps && event.extendedProps.is_regular);

                        if (isRegular) {
                            // Re-render and clone the event across every visible calendar frame context
                            for (let y = startYear; y <= endYear; y++) {
                                let clonedEvent = { ...event }; 
                                
                                // Strip out original base year characters (YYYY-MM-DD -> MM-DD)
                                let monthDayStr = String(clonedEvent.start).substring(5, 10); 
                                clonedEvent.start = `${y}-${monthDayStr}`;
                                
                                if (clonedEvent.end) {
                                    let endMonthDayStr = String(clonedEvent.end).substring(5, 10);
                                    clonedEvent.end = `${y}-${endMonthDayStr}`;
                                }
                                
                                // Prevent engine conflicts via dynamically unique identification hashes
                                clonedEvent.id = `${event.id || 'holiday'}-infinite-${y}`; 
                                dynamicEvents.push(clonedEvent);
                            }
                        } else {
                            // Standard leaves and single-day event profiles flow out naturally 
                            dynamicEvents.push(event);
                        }
                    });

                    successCallback(dynamicEvents);
                },
                
                eventContent: function(arg) {
                    const isLeaveRequest = arg.event.extendedProps && arg.event.extendedProps.leave_id;
                    const textColorStyle = isLeaveRequest ? '' : `color: ${arg.event.textColor || '#ffffff'};`;

                    return {
                        html: `
                            <div style="display: flex; align-items: center; width: 100%; overflow: hidden; padding: 0 4px;">
                                <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; ${textColorStyle}">
                                    ${arg.event.title}
                                </span>
                            </div>
                        `
                    };
                }
            }); 
            
            
            calendar.render();

            // 3. 🌟 UP/DOWN CLICK WARPING INTERFACES (Linked directly to FullCalendar Engine)
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
        });
    </script>
</x-app-layout>