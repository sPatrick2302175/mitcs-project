<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin: Review Leave Application') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-6">
                <a href="{{ route('admin.leave-requests.index') }}" class="inline-flex items-center text-sm font-semibold text-gray-500 hover:text-gray-800 transition">
                    ← Back to Management Masterlist
                </a>
            </div>

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-r-lg shadow-sm">
                    <div class="font-bold mb-1">Please address the processing errors below:</div>
                    <ul class="list-disc pl-5 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm rounded-xl p-8 space-y-8">
                
                <div class="flex justify-between items-start border-b border-gray-100 pb-6">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-widest text-indigo-600 block mb-1">Civil Service Form No. 6</span>
                        <h3 class="text-2xl font-black text-gray-900">
                            {{ $leaveRequest->employee->first_name ?? '' }} {{ $leaveRequest->employee->last_name ?? 'Unknown Employee' }}
                        </h3>
                        <p class="text-sm text-gray-500 mt-0.5">
                            ID Number: <span class="font-semibold text-gray-700">{{ $leaveRequest->employee->employee_id_number ?? 'N/A' }}</span>
                        </p>
                    </div>

                    <div>
                        @if($leaveRequest->status === 'pending')
                            <span class="px-4 py-1.5 text-xs font-bold uppercase tracking-wider rounded-full bg-yellow-100 text-yellow-800 ring-4 ring-yellow-50">
                                Pending Review
                            </span>
                        @elseif($leaveRequest->status === 'approved')
                            <span class="px-4 py-1.5 text-xs font-bold uppercase tracking-wider rounded-full bg-green-100 text-green-800 ring-4 ring-green-50">
                                Approved Record
                            </span>
                        @else
                            <span class="px-4 py-1.5 text-xs font-bold uppercase tracking-wider rounded-full bg-red-100 text-red-800 ring-4 ring-red-50">
                                Disapproved Record
                            </span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    
                    <div class="space-y-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400">Application Details</h4>
                        
                        <div class="bg-slate-50/60 p-4 rounded-xl space-y-3 border border-slate-100/50">
                            <div>
                                <label class="text-xs text-gray-400 block">Type of Leave Requested</label>
                                <span class="font-bold text-gray-800 text-base">{{ $leaveRequest->leave_type }}</span>
                                @if($leaveRequest->leave_type === 'Others' && $leaveRequest->leave_type_others)
                                    <span class="block text-sm text-indigo-600 font-semibold mt-0.5">({{ $leaveRequest->leave_type_others }})</span>
                                @endif
                            </div>

                            @if($leaveRequest->leave_detail_category || $leaveRequest->leave_detail_specifics)
                                <div class="pt-2 border-t border-slate-200/40">
                                    <label class="text-xs text-gray-400 block">Details / Context Specifications</label>
                                    <span class="text-sm font-medium text-gray-700 block">{{ $leaveRequest->leave_detail_category ?? 'N/A' }}</span>
                                    <span class="text-xs text-gray-500 block italic mt-0.5">"{{ $leaveRequest->leave_detail_specifics ?? 'No supplementary notes' }}"</span>
                                </div>
                            @endif

                            <div class="pt-2 border-t border-slate-200/40 grid grid-cols-2 gap-2">
                                <div>
                                    <label class="text-xs text-gray-400 block">Inclusive Range</label>
                                    <span class="text-sm font-bold text-gray-800">
                                        {{ \Carbon\Carbon::parse($leaveRequest->start_date)->format('M d, Y') }}
                                    </span>
                                    <span class="text-xs text-gray-400 block">to {{ \Carbon\Carbon::parse($leaveRequest->end_date)->format('M d, Y') }}</span>
                                </div>
                                <div>
                                    <label class="text-xs text-gray-400 block">Days Claimed</label>
                                    <span class="text-xl font-black text-slate-800">{{ number_format($leaveRequest->working_days_applied, 1) }} Days</span>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-slate-200/40">
                                <label class="text-xs text-gray-400 block">Commutation Requested?</label>
                                <span class="text-xs font-bold uppercase {{ $leaveRequest->commutation_requested ? 'text-emerald-600' : 'text-slate-500' }}">
                                    {{ $leaveRequest->commutation_requested ? 'Yes (Requested)' : 'No (Not Requested)' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400">Current Employee Leave Balances</h4>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-3 bg-white border border-gray-100 rounded-xl shadow-sm hover:shadow-md transition">
                                <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-tight">Vacation Leave</span>
                                <span class="text-xl font-black text-gray-800">{{ number_format($leaveRequest->employee->vacation_leave_balance ?? 0, 2) }}</span>
                            </div>
                            <div class="p-3 bg-white border border-gray-100 rounded-xl shadow-sm hover:shadow-md transition">
                                <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-tight">Sick Leave</span>
                                <span class="text-xl font-black text-gray-800">{{ number_format($leaveRequest->employee->sick_leave_balance ?? 0, 2) }}</span>
                            </div>
                            <div class="p-3 bg-white border border-gray-100 rounded-xl shadow-sm hover:shadow-md transition">
                                <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-tight">Mandatory/Forced</span>
                                <span class="text-xl font-black text-gray-800">{{ number_format($leaveRequest->employee->mandatory_leave_balance ?? 0, 2) }}</span>
                            </div>
                            <div class="p-3 bg-white border border-gray-100 rounded-xl shadow-sm hover:shadow-md transition">
                                <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-tight">Special Privilege</span>
                                <span class="text-xl font-black text-gray-800">{{ number_format($leaveRequest->employee->special_privilege_leave_balance ?? 0, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-6">
                    
                    @if($leaveRequest->status === 'pending')
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Section 7: Details of Action on Application</h3>
                        
                        <form action="{{ route('admin.leave-requests.action', $leaveRequest->id) }}" method="POST" class="space-y-6">
                            @csrf
                            
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wider text-gray-500 block mb-2">Final Action Decision</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <label class="border-2 border-slate-100 rounded-xl p-4 flex items-center space-x-3 cursor-pointer hover:bg-slate-50 transition peer-checked:border-slate-900">
                                        <input type="radio" name="status" value="approved" id="action-approve" checked class="text-slate-900 focus:ring-slate-900">
                                        <div>
                                            <span class="block font-bold text-gray-800 text-sm">Approve Application</span>
                                            <span class="block text-xs text-gray-400">Deducts days from selected category matching input criteria.</span>
                                        </div>
                                    </label>
                                    
                                    <label class="border-2 border-slate-100 rounded-xl p-4 flex items-center space-x-3 cursor-pointer hover:bg-slate-50 transition">
                                        <input type="radio" name="status" value="disapproved" id="action-disapprove" class="text-red-600 focus:ring-red-500">
                                        <div>
                                            <span class="block font-bold text-gray-800 text-sm">Disapprove Application</span>
                                            <span class="block text-xs text-gray-400">Rejects application and leaves current balances untouched.</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div id="approval-inputs-panel" class="bg-slate-50/50 p-6 rounded-xl border border-slate-100 space-y-4">
                                <h5 class="text-xs font-bold uppercase tracking-wider text-gray-500">Approval Parameters Allocation</h5>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="days_with_pay" class="text-xs font-semibold text-gray-600 block mb-1">Days With Pay</label>
                                        <input type="number" step="0.5" min="0" name="days_with_pay" id="days_with_pay" 
                                               value="{{ old('days_with_pay', $leaveRequest->working_days_applied) }}"
                                               class="w-full rounded-lg border-gray-200 text-sm focus:border-slate-900 focus:ring-slate-900">
                                    </div>
                                    <div>
                                        <label for="days_without_pay" class="text-xs font-semibold text-gray-600 block mb-1">Days Without Pay</label>
                                        <input type="number" step="0.5" min="0" name="days_without_pay" id="days_without_pay" 
                                               value="{{ old('days_without_pay', 0) }}"
                                               class="w-full rounded-lg border-gray-200 text-sm focus:border-slate-900 focus:ring-slate-900">
                                    </div>
                                </div>

                                <div>
                                    <label for="recommendation_reason" class="text-xs font-semibold text-gray-600 block mb-1">Recommendation / Approval Remarks (Optional)</label>
                                    <textarea name="recommendation_reason" id="recommendation_reason" rows="2" 
                                              placeholder="Provide explicit notes concerning approval details..."
                                              class="w-full rounded-lg border-gray-200 text-sm focus:border-slate-900 focus:ring-slate-900">{{ old('recommendation_reason') }}</textarea>
                                </div>
                            </div>

                            <div id="disapproval-inputs-panel" class="bg-red-50/30 p-6 rounded-xl border border-red-100/50 space-y-3 hidden">
                                <h5 class="text-xs font-bold uppercase tracking-wider text-red-700">Disapproval Justification Required</h5>
                                <div>
                                    <label for="disapproval_reason" class="text-xs font-semibold text-gray-600 block mb-1">Reason for Disapproval</label>
                                    <textarea name="disapproval_reason" id="disapproval_reason" rows="3" 
                                              placeholder="Specify clear legal/operational grounds for rejection as mandated by Civil Service guidelines..."
                                              class="w-full rounded-lg border-gray-200 text-sm focus:border-red-500 focus:ring-red-500">{{ old('disapproval_reason') }}</textarea>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-gray-100 flex justify-end">
                                <button type="submit" class="inline-flex items-center px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold rounded-xl shadow-md transition duration-150 tracking-wide">
                                    COMMIT TRANSACTION DATA
                                </button>
                            </div>
                        </form>

                    @else
                        <div class="bg-slate-50 p-6 rounded-xl border border-slate-100 space-y-4">
                            <h3 class="text-base font-bold text-slate-800 flex items-center">
                                📝 Historical Transaction Audit Trail Log
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                                <div class="space-y-2">
                                    <div>
                                        <span class="text-xs text-gray-400 block">Processing Action Status</span>
                                        <span class="font-bold capitalize text-gray-800">{{ $leaveRequest->status }}</span>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-400 block">Days With Pay Credited</span>
                                        <span class="font-semibold text-gray-700">{{ $leaveRequest->days_with_pay ?? 0 }} Days</span>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-400 block">Days Without Pay Assigned</span>
                                        <span class="font-semibold text-gray-700">{{ $leaveRequest->days_without_pay ?? 0 }} Days</span>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    @if($leaveRequest->status === 'approved')
                                        <div>
                                            <span class="text-xs text-gray-400 block">Approval Executive Remarks</span>
                                            <p class="text-gray-700 italic">"{{ $leaveRequest->recommendation_reason ?? 'No internal annotations recorded.' }}"</p>
                                        </div>
                                    @else
                                        <div>
                                            <span class="text-xs text-gray-400 block">Official Rejection Log Reasons</span>
                                            <p class="text-red-700 font-medium bg-red-50 p-3 rounded-lg border border-red-100 mt-1">
                                                {{ $leaveRequest->disapproval_reason ?? 'No disapproval explanation was supplied by processing officer.' }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="pt-4 border-t border-slate-200 flex justify-between items-center">
                                <span class="text-xs text-gray-400 font-medium">Form tracking item immutable. System logs locked.</span>
                                <a href="{{ route('admin.leave-requests.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs rounded-lg transition">
                                    Return to Masterlist
                                </a>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const radioApprove = document.getElementById('action-approve');
            const radioDisapprove = document.getElementById('action-disapprove');
            const approvalPanel = document.getElementById('approval-inputs-panel');
            const disapprovalPanel = document.getElementById('disapproval-inputs-panel');

            if (radioApprove && radioDisapprove) {
                function togglePanels() {
                    if (radioApprove.checked) {
                        approvalPanel.classList.remove('hidden');
                        disapprovalPanel.classList.add('hidden');
                    } else if (radioDisapprove.checked) {
                        approvalPanel.classList.add('hidden');
                        disapprovalPanel.classList.remove('hidden');
                    }
                }

                radioApprove.addEventListener('change', togglePanels);
                radioDisapprove.addEventListener('change', togglePanels);
            }
        });
    </script>
</x-app-layout>