<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Leave Requests Management Masterlist') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 rounded-r-lg shadow-sm font-medium text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100">
                <div class="p-6 bg-white border-b border-gray-100 flex justify-between items-center flex-wrap gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Civil Service Form No. 6 Applications</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Review, certify, and log final action outcomes for corporate leave allocations.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-gray-100 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                <th class="py-4 px-6">Employee Metadata</th>
                                <th class="py-4 px-4">Leave Parameters</th>
                                <th class="py-4 px-4">Inclusive Range</th>
                                <th class="py-4 px-4 text-center">Days</th>
                                <th class="py-4 px-4 text-center">Status</th>
                                <th class="py-4 px-6 text-right">Processing Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                            @forelse($leaveRequests as $request)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-gray-900 text-sm">
                                            {{ $request->employee->first_name ?? '' }} {{ $request->employee->last_name ?? 'Unknown Employee' }}
                                        </div>
                                        <div class="text-xs text-gray-400 mt-0.5">
                                            {{-- Fixed: Now explicitly uses employee_id_number --}}
                                            ID: <span class="font-medium text-gray-600">{{ $request->employee->employee_id_number ?? 'N/A' }}</span>
                                        </div>
                                    </td>

                                    <td class="py-4 px-4">
                                        <span class="font-semibold text-slate-800">{{ $request->leave_type }}</span>
                                        @if($request->leave_type === 'Others' && $request->leave_type_others)
                                            <span class="block text-xs text-indigo-600 font-medium">({{ $request->leave_type_others }})</span>
                                        @endif
                                    </td>

                                    <td class="py-4 px-4">
                                        <div class="font-medium text-gray-800">
                                            {{ \Carbon\Carbon::parse($request->start_date)->format('M d, Y') }}
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            to {{ \Carbon\Carbon::parse($request->end_date)->format('M d, Y') }}
                                        </div>
                                    </td>

                                    <td class="py-4 px-4 text-center font-bold text-gray-900">
                                        {{ number_format($request->working_days_applied, 1) }}
                                    </td>

                                    <td class="py-4 px-4 text-center">
                                        @if($request->status === 'pending')
                                            <span class="inline-flex px-2.5 py-1 text-xs font-bold uppercase tracking-wide rounded-md bg-yellow-50 text-yellow-700 border border-yellow-200">
                                                Pending
                                            </span>
                                        @elseif($request->status === 'approved')
                                            <span class="inline-flex px-2.5 py-1 text-xs font-bold uppercase tracking-wide rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Approved
                                            </span>
                                        @else
                                            <span class="inline-flex px-2.5 py-1 text-xs font-bold uppercase tracking-wide rounded-md bg-red-50 text-red-700 border border-red-200">
                                                Disapproved
                                            </span>
                                        @endif
                                    </td>

                                    <td class="py-4 px-6 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center space-x-2">
                                            <a href="{{ route('admin.leave-requests.review', $request->id) }}" 
                                               class="inline-flex items-center px-3 py-1.5 {{ $request->status === 'pending' ? 'bg-slate-900 hover:bg-slate-800 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }} text-xs font-bold rounded-lg transition shadow-sm">
                                                @if($request->status === 'pending')
                                                    Review & Action
                                                @else
                                                    View Record
                                                @endif
                                            </a>

                                            @if($request->status === 'approved')
                                                <a href="{{ route('leave-requests.pdf', $request->id) }}" 
                                                   target="_blank" 
                                                   class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition shadow-sm">
                                                    Print Form 6
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-sm text-gray-400">
                                        <div class="text-base font-semibold text-gray-500 mb-1">No applications logged</div>
                                        There are currently no active or historic leave requests found in the system databases.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(method_exists($leaveRequests, 'links'))
                    <div class="p-4 bg-slate-50 border-t border-gray-100">
                        {{ $leaveRequests->links() }}
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>