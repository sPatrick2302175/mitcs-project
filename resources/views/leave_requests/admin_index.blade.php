<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Leave Requests Management Masterlist') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-8 bg-emerald-50/70 backdrop-blur-sm border border-emerald-100 rounded-xl p-5 shadow-sm transition-all duration-300 animate-fadeIn">
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

            <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 overflow-hidden transition-all duration-300">
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
                                            @if($request->status === 'pending')
                                                Review & Action
                                            @else
                                                View Record
                                            @endif
                                        </a>

                                        @if($request->status === 'approved')
                                            <a href="{{ route('leave-requests.pdf', $request->id) }}" target="_blank" 
                                                class="inline-flex items-center px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-xs font-bold rounded-xl transition-all duration-200 shadow-md shadow-gray-900/10 active:scale-[0.98]">
                                                Print Form 6
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
                                        <p class="text-sm text-gray-500 font-medium">There are currently no active or historic leave requests found in the system databases.</p>
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
</x-app-layout>