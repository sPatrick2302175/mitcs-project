<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('My Leave Dashboard') }}
        </h2>
    </x-slot>

    <!-- Custom FullCalendar Theme Overrides -->
    <style>
        .fc {
            font-family: inherit;
        }
        .fc-theme-standard td, .fc-theme-standard th {
            border-color: #f3f4f6 !important;
        }
        .fc-scrollgrid {
            border-radius: 1rem;
            overflow: hidden;
            border-color: #f3f4f6 !important;
        }
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
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .fc .fc-button-primary:not(:disabled):active {
            transform: scale(0.98);
        }
        .fc .fc-toolbar-title {
            font-size: 1.25rem !important;
            font-weight: 900 !important;
            color: #1f2937;
            letter-spacing: -0.025em;
        }
        .fc-col-header-cell-cushion {
            font-size: 0.7rem;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: 0.05em;
            color: #6b7280;
            padding: 0.75rem 0 !important;
        }
        .fc-daygrid-day-number {
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            padding: 0.5rem !important;
        }
        .fc-day-today {
            background-color: #fffbeb !important;
        }
        .fc-event {
            border: none !important;
            border-radius: 0.5rem !important;
            padding: 0.15rem 0.25rem;
            font-size: 0.7rem;
            font-weight: 700;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
    </style>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- Success Alert -->
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

            <!-- Balance Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Vacation Leave -->
                <div class="bg-white border border-gray-100/60 rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 relative overflow-hidden group">
                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-indigo-50 rounded-full blur-2xl opacity-50 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="relative z-10">
                        <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400 block mb-1">Vacation Leave</span>
                        <div class="flex items-baseline space-x-1.5">
                            <span class="text-3xl font-black text-gray-800">{{ number_format($employee->vacation_leave_balance, 2) }}</span>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Days</span>
                        </div>
                    </div>
                </div>

                <!-- Sick Leave -->
                <div class="bg-white border border-gray-100/60 rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 relative overflow-hidden group">
                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-50 rounded-full blur-2xl opacity-50 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="relative z-10">
                        <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400 block mb-1">Sick Leave</span>
                        <div class="flex items-baseline space-x-1.5">
                            <span class="text-3xl font-black text-gray-800">{{ number_format($employee->sick_leave_balance, 2) }}</span>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Days</span>
                        </div>
                    </div>
                </div>

                <!-- Mandatory Leave -->
                <div class="bg-white border border-gray-100/60 rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 relative overflow-hidden group">
                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-amber-50 rounded-full blur-2xl opacity-50 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="relative z-10">
                        <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400 block mb-1">Mandatory Leave</span>
                        <div class="flex items-baseline space-x-1.5">
                            <span class="text-3xl font-black text-gray-800">{{ number_format($employee->mandatory_leave_balance, 2) }}</span>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Days</span>
                        </div>
                    </div>
                </div>

                <!-- Special Privilege -->
                <div class="bg-white border border-gray-100/60 rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 relative overflow-hidden group">
                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-purple-50 rounded-full blur-2xl opacity-50 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="relative z-10">
                        <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400 block mb-1">Special Privilege</span>
                        <div class="flex items-baseline space-x-1.5">
                            <span class="text-3xl font-black text-gray-800">{{ number_format($employee->special_privilege_leave_balance, 2) }}</span>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Days</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Section -->
            <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 overflow-hidden transition-all duration-300">
                <div class="p-6 md:p-8 border-b border-gray-100/60 flex flex-col md:flex-row justify-between items-center gap-4 bg-white">
                    <h3 class="text-xl font-black text-gray-800 tracking-tight">
                        My Leave Applications History
                    </h3>
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
                            @forelse($leaveRequests as $request)
                                <tr class="hover:bg-gray-50/30 transition-colors duration-200">
                                    <td class="py-4 px-6 text-sm font-semibold text-gray-800 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($request->date_of_filing)->format('M d, Y') }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="text-sm font-bold text-gray-700 block">{{ $request->leave_type }}</span>
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
                                    <td class="py-4 px-6 text-center whitespace-nowrap">
                                        @if($request->status !== 'pending')
                                            <a href="{{ route('leave-requests.pdf', $request->id) }}" class="inline-flex items-center justify-center px-3 py-1.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 hover:text-indigo-800 font-bold text-[10px] uppercase tracking-wider rounded-lg border border-indigo-100/60 transition-colors">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                View PDF
                                            </a>
                                        @else
                                            <span class="text-gray-400 text-[10px] font-bold uppercase tracking-wider bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100/80">
                                                Locked
                                            </span>
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
                                            <span class="text-sm font-medium text-gray-500">No leave applications found.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- FullCalendar Section -->
            <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 overflow-hidden p-6 md:p-8">
                <div class="mb-6">
                    <h3 class="text-xl font-black text-gray-800 tracking-tight">Company Calendar & Holidays</h3>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mt-1">Global view of scheduled events and corporate leaves</p>
                </div>
                <div id="calendar" class="min-h-[500px]"></div>
            </div>
            
        </div>
    </div>

    <!-- FullCalendar Script -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            
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
                events: @json($calendarEvents ?? []),
            });
            
            calendar.render();
        });
    </script>
</x-app-layout>