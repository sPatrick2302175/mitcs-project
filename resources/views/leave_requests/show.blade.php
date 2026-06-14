<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('My Leave Application Details') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-6">
                <a href="{{ route('employee.leave-requests.index') }}" class="inline-flex items-center text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors group">
                    <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to My Leave Applications
                </a>
            </div>

            <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 overflow-hidden transition-all duration-300">
                
                <div class="p-6 md:p-8 border-b border-gray-100/60 flex flex-col md:flex-row justify-between items-start gap-6 bg-white">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-widest text-[#F2A455] block mb-1.5">Civil Service Form No. 6</span>
                        <h3 class="text-3xl font-black text-gray-800 tracking-tight">
                            {{ $leaveRequest->employee->first_name ?? '' }} {{ $leaveRequest->employee->last_name ?? 'Unknown Employee' }}
                        </h3>
                        <p class="text-sm text-gray-500 font-medium mt-1.5">
                            ID Number: <span class="bg-gray-50 text-gray-700 px-2 py-0.5 rounded-md border border-gray-100 font-bold ml-1">{{ $leaveRequest->employee->employee_id_number ?? 'N/A' }}</span>
                        </p>
                    </div>

                    <div class="shrink-0">
                        @if($leaveRequest->status === 'pending')
                            <span class="inline-flex items-center px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-xl bg-amber-50 text-amber-600 border border-amber-100/60 shadow-sm">
                                <span class="w-2 h-2 rounded-full bg-amber-500 mr-2 animate-pulse"></span>
                                Pending Review
                            </span>
                        @elseif($leaveRequest->status === 'approved')
                            <span class="inline-flex items-center px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100/60 shadow-sm">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Approved
                            </span>
                        @else
                            <span class="inline-flex items-center px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-xl bg-rose-50 text-rose-600 border border-rose-100/60 shadow-sm">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                Disapproved
                            </span>
                        @endif
                    </div>
                </div>

                <div class="p-6 md:p-8 space-y-10">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        
                        <div class="space-y-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400">Application Details</h4>
                            
                            <div class="bg-gray-50/50 p-6 rounded-2xl border border-gray-100/60 space-y-4">
                                <div>
                                    <label class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1">Type of Leave Requested</label>
                                    <span class="font-extrabold text-gray-800 text-lg">{{ $leaveRequest->leave_type }}</span>
                                    @if($leaveRequest->leave_type === 'Others' && $leaveRequest->leave_type_others)
                                        <span class="block text-xs font-bold text-[#F2A455] uppercase tracking-wider mt-1">({{ $leaveRequest->leave_type_others }})</span>
                                    @endif
                                </div>

                                @if($leaveRequest->leave_detail_category || $leaveRequest->leave_detail_specifics)
                                    <div class="pt-3 border-t border-gray-200/50">
                                        <label class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1">Details / Context Specifications</label>
                                        <span class="text-sm font-bold text-gray-700 block">{{ $leaveRequest->leave_detail_category ?? 'N/A' }}</span>
                                        <span class="text-sm text-gray-500 font-medium block italic mt-1 bg-white p-3 rounded-xl border border-gray-100 shadow-sm">"{{ $leaveRequest->leave_detail_specifics ?? 'No supplementary notes' }}"</span>
                                    </div>
                                @endif

                                <div class="pt-3 border-t border-gray-200/50 grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1">Inclusive Range</label>
                                        <span class="text-sm font-extrabold text-gray-800 block">
                                            {{ \Carbon\Carbon::parse($leaveRequest->start_date)->format('M d, Y') }}
                                        </span>
                                        <span class="text-xs text-gray-500 font-medium block mt-0.5">to {{ \Carbon\Carbon::parse($leaveRequest->end_date)->format('M d, Y') }}</span>
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1">Days Claimed</label>
                                        <span class="text-2xl font-black text-[#F2A455]">{{ number_format($leaveRequest->working_days_applied, 1) }} <span class="text-xs text-gray-500 font-bold uppercase tracking-wider">Days</span></span>
                                    </div>
                                </div>

                                <div class="pt-3 border-t border-gray-200/50">
                                    <label class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1">Commutation Requested?</label>
                                    <span class="inline-flex px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg {{ $leaveRequest->commutation_requested ? 'bg-emerald-50 text-emerald-600 border border-emerald-100/60' : 'bg-gray-100 text-gray-500 border border-gray-200/60' }} shadow-sm">
                                        {{ $leaveRequest->commutation_requested ? 'Yes (Requested)' : 'No (Not Requested)' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400">Your Leave Balances</h4>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-5 bg-white border border-gray-100/60 rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300">
                                    <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400 block mb-1">Vacation Leave</span>
                                    <span class="text-2xl font-black text-gray-800">{{ number_format($leaveRequest->employee?->leaveBalance?->vacation_leave_balance ?? 0, 2) }}</span>
                                </div>                                            
                                <div class="p-5 bg-white border border-gray-100/60 rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300">
                                    <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400 block mb-1">Sick Leave</span>
                                    <span class="text-2xl font-black text-gray-800">{{ number_format($leaveRequest->employee->leaveBalance?->sick_leave_balance ?? 0, 2) }}</span>
                                </div>
                                <div class="p-5 bg-white border border-gray-100/60 rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300">
                                    <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400 block mb-1">Mandatory/Forced</span>
                                    <span class="text-2xl font-black text-gray-800">{{ number_format($leaveRequest->employee->leaveBalance?->mandatory_leave_balance ?? 0, 2) }}</span>
                                </div>
                                <div class="p-5 bg-white border border-gray-100/60 rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300">
                                    <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400 block mb-1">Special Privilege</span>
                                    <span class="text-2xl font-black text-gray-800">{{ number_format($leaveRequest->employee->leaveBalance?->special_privilege_leave_balance ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-8 border-t border-gray-100/80">
                        
                        @if($leaveRequest->status === 'pending')
                            <div class="bg-amber-50/50 border border-amber-100 rounded-2xl p-6 md:p-8 flex flex-col sm:flex-row justify-between items-center gap-6">
                                <div class="flex items-start">
                                    <div class="shrink-0 bg-amber-100 p-2.5 rounded-xl text-amber-600">
                                        <svg class="h-6 w-6 animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="ms-4">
                                        <h4 class="text-sm font-extrabold text-amber-900 tracking-tight">Section 7: Status Update</h4>
                                        <p class="text-sm font-medium text-amber-700 mt-1">This application is currently waiting for validation and processing actions from Human Resources / Administration.</p>
                                    </div>
                                </div>
                                <a href="{{ route('employee.leave-requests.index') }}" class="w-full sm:w-auto text-center inline-flex justify-center items-center px-6 py-2.5 bg-white border border-gray-200/80 hover:bg-gray-50 text-gray-700 font-extrabold text-xs uppercase tracking-wider rounded-xl transition-all duration-200 shadow-sm active:scale-[0.98]">
                                    Return to My Requests
                                </a>
                            </div>

                        @else
                            <div class="bg-gray-50/50 p-6 md:p-8 rounded-2xl border border-gray-100/60 space-y-6">
                                <h3 class="text-lg font-extrabold text-gray-800 tracking-tight flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    Section 7: Details of Action on Application (Processed Log)
                                </h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="space-y-4">
                                        <div>
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1">Processing Action Status</span>
                                            <span class="font-extrabold capitalize {{ $leaveRequest->status === 'approved' ? 'text-emerald-600' : 'text-rose-600' }}">{{ $leaveRequest->status }}</span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200/50">
                                            <div>
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1">Days With Pay Credited</span>
                                                <span class="font-black text-gray-800">{{ number_format($leaveRequest->days_with_pay ?? 0, 1) }} Days</span>
                                            </div>
                                            <div>
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1">Days Without Pay</span>
                                                <span class="font-black text-gray-800">{{ number_format($leaveRequest->days_without_pay ?? 0, 1) }} Days</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        @if($leaveRequest->status === 'approved')
                                            <div>
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-2">Approval Executive Remarks</span>
                                                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                                                    <p class="text-sm font-medium text-gray-600 italic">"{{ $leaveRequest->recommendation_reason ?? 'No internal annotations recorded.' }}"</p>
                                                </div>
                                            </div>
                                        </@else
                                            <div>
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-2">Official Rejection Log Reasons</span>
                                                <div class="bg-rose-50 p-4 rounded-xl border border-rose-100 shadow-sm">
                                                    <p class="text-sm font-bold text-rose-700">
                                                        {{ $leaveRequest->disapproval_reason ?? 'No disapproval explanation was supplied by processing officer.' }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="pt-6 border-t border-gray-200/60 flex flex-col sm:flex-row justify-between items-center gap-4">
                                    <div class="flex items-center text-xs text-gray-400 font-bold uppercase tracking-wider">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        Form tracking item immutable. System logs locked.
                                    </div>
                                    <a href="{{ route('employee.leave-requests.index') }}" class="inline-flex items-center px-6 py-2.5 bg-white border border-gray-200/80 hover:bg-gray-50 text-gray-700 font-extrabold text-xs uppercase tracking-wider rounded-xl transition-all duration-200 shadow-sm active:scale-[0.98]">
                                        Return to My Requests
                                    </a>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>