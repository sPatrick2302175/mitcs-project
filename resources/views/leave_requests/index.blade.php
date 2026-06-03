<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Leave Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-6 px-4 py-3 text-green-700 bg-green-100 rounded-lg shadow-sm border border-green-400">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-indigo-500">
                    <div class="text-sm font-medium text-gray-500 uppercase">Vacation Leave</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($employee->vacation_leave_balance, 2) }}</div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-emerald-500">
                    <div class="text-sm font-medium text-gray-500 uppercase">Sick Leave</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($employee->sick_leave_balance, 2) }}</div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-amber-500">
                    <div class="text-sm font-medium text-gray-500 uppercase">Mandatory Leave</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($employee->mandatory_leave_balance, 2) }}</div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-purple-500">
                    <div class="text-sm font-medium text-gray-500 uppercase">Special Privilege</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($employee->special_privilege_leave_balance, 2) }}</div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-8">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900">My Leave Applications History</h3>
                    <a href="{{ route('leave-requests.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150">
                        + Apply for Leave
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-center border-collapse">
                        <thead>
                            <tr class="border-b bg-gray-50">
                                <th class="py-3 px-4 text-sm font-semibold text-gray-600 text-center">Date Filed</th>
                                <th class="py-3 px-4 text-sm font-semibold text-gray-600 text-center">Type</th>
                                <th class="py-3 px-4 text-sm font-semibold text-gray-600 text-center">Inclusive Dates</th>
                                <th class="py-3 px-4 text-sm font-semibold text-gray-600 text-center">Days</th>
                                <th class="py-3 px-4 text-sm font-semibold text-gray-600 text-center">Status</th>
                                <th class="py-3 px-4 text-sm font-semibold text-gray-600 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leaveRequests as $request)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4 text-sm text-gray-900 align-middle text-center">{{ \Carbon\Carbon::parse($request->date_of_filing)->format('M d, Y') }}</td>
                                    <td class="py-3 px-4 text-sm text-gray-700 align-middle text-center">{{ $request->leave_type }}</td>
                                    <td class="py-3 px-4 text-sm text-gray-600 align-middle text-center">
                                        {{ \Carbon\Carbon::parse($request->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($request->end_date)->format('M d, Y') }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-600 align-middle text-center">{{ $request->working_days_applied }}</td>
                                    
                                    <td class="py-3 px-4 text-sm font-medium align-middle text-center">
                                        <span class="inline-flex px-2 py-1 rounded text-xs font-semibold
                                            {{ $request->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $request->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $request->status === 'disapproved' ? 'bg-red-100 text-red-800' : '' }}">
                                            {{ ucfirst($request->status) }}
                                        </span>
                                    </td>
                                    
                                    <td class="py-3 px-4 text-sm align-middle text-center">
                                        @if($request->status !== 'pending')
                                            <a href="{{ route('leave-requests.pdf', $request->id) }}" class="inline-flex items-center justify-center text-indigo-600 hover:text-indigo-900 font-semibold text-xs uppercase tracking-wider">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                PDF
                                            </a>
                                        @else
                                            <span class="text-gray-400 text-xs uppercase tracking-wider inline-flex justify-center">Awaiting Action</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-4 text-center text-sm text-gray-500 align-middle">No leave applications found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- NEW: FullCalendar Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-6">Company Calendar & Holidays</h3>
                <div id="calendar"></div>
            </div>
            
        </div>
    </div>

    <!-- FullCalendar Script (Loads via CDN) -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 'auto', // Adjusts to fit nicely inside your layout
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,dayGridWeek'
                },
                // Here is where we inject the Laravel PHP variable into the Javascript!
                events: @json($calendarEvents ?? []),
            });
            
            calendar.render();
        });
    </script>
</x-app-layout>