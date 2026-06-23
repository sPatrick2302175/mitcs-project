<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ request()->routeIs('admin.*') ? __('Admin: Review Leave Application') : __('My Leave Application Details') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-6">
                @if(request()->routeIs('admin.*'))
                    <a href="{{ route('admin.leave-requests.index') }}" class="inline-flex items-center text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors group">
                        <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Back to Management Masterlist
                    </a>
                @elseif(request()->query('from') === 'history')
                
                    <a href="{{ route('leave-requests.history') }}" class="inline-flex items-center text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors group">
                        <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Back to History
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors group">
                        <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Back to Leave Dashboard
                    </a>
                @endif
            </div>

            @if($errors->any())
                <div class="mb-8 bg-rose-50/70 backdrop-blur-sm border border-rose-100 rounded-2xl p-6 shadow-sm transition-all duration-300 animate-fadeIn">
                    <div class="flex items-start">
                        <div class="shrink-0 bg-rose-100 p-2.5 rounded-xl">
                            <svg class="h-5 w-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div class="ms-4">
                            <h3 class="text-sm font-extrabold text-rose-800 tracking-tight mb-2">Please address the processing errors below:</h3>
                            <ul class="list-disc pl-5 text-sm font-medium text-rose-700 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 overflow-hidden transition-all duration-300">
                
                <div class="p-6 md:p-8 border-b border-gray-100/60 flex flex-col md:flex-row justify-between items-start gap-6 bg-white">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-widest text-[#F2A455] block mb-1.5">Civil Service Form No. 6</span>
                        <h3 class="text-3xl font-black tracking-tight text-gray-800">
                            @if(auth()->check() && auth()->user()->is_admin !== 0)
                                <a href="{{ route('employees.show', ['employee' => $leaveRequest->employee_id, 'from' => 'review', 'request_id' => $leaveRequest->id]) }}" class="cursor-pointer" title="View Employee Profile">
                                    {{ $leaveRequest->employee->first_name ?? '' }} {{ $leaveRequest->employee->last_name ?? 'Unknown Employee' }}
                                    
                                    <svg class="w-6 h-6 ml-2 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                </a>
                            @else
                                <span>{{ $leaveRequest->employee->first_name ?? '' }} {{ $leaveRequest->employee->last_name ?? 'Unknown Employee' }}</span>
                            @endif
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
                                Approved Record
                            </span>
                            <a href="{{ route('leave-requests.pdf', $leaveRequest->id) }}" class="inline-flex items-center justify-center px-3 py-1.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 hover:text-indigo-800 font-bold text-[10px] uppercase tracking-wider rounded-lg border border-indigo-100/60 transition-colors shadow-sm">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                View PDF
                            </a>
                        
                        @else
                            <span class="inline-flex items-center px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-xl bg-rose-50 text-rose-600 border border-rose-100/60 shadow-sm">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                Disapproved Record
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
                                    <span class="font-extrabold text-gray-800 text-lg">
                                        {{ $leaveRequest->leaveType->leave_type_name ?? 'Standard Leave' }}
                                    </span>
                                    
                                    @if(str_contains($leaveRequest->leaveType->leave_type_name ?? '', 'Others') && $leaveRequest->leave_type_others)
                                        <span class="block text-xs font-bold text-[#F2A455] uppercase tracking-wider mt-1">
                                            ({{ $leaveRequest->leave_type_others }})
                                        </span>
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
                                    <div class="pr-2">
                                        <label class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1">Inclusive Range</label>
                                        <span class="text-sm font-extrabold text-[#F2A455] block leading-relaxed">
                                            {{ $leaveRequest->formatted_inclusive_dates }}
                                        </span>
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1">
                                            @if($leaveRequest->status === 'pending')
                                                Days Requested
                                            @elseif($leaveRequest->status === 'disapproved')
                                                Days Denied
                                            @else
                                                Days Claimed
                                            @endif
                                        </label>
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
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400">
                                {{ $leaveRequest->status === 'pending' ? 'Current Employee Leave Balances' : 'Leave Credit Impact Summary' }}
                            </h4>
                            
                            @php
                                $indexedBalances = $leaveRequest->employee->leaveBalances->keyBy('leave_type_id');
                                $allLeaveTypes = \App\Models\LeaveType::whereIn('code', ['VL', 'SL', 'FL', 'SPL'])->get();
                            @endphp

                            <div class="grid grid-cols-2 gap-4">
                                @foreach($allLeaveTypes as $type)
                                    @php
                                        // 1. Resolve "Before Request" via snapshots. Fall back to current data for legacy records.
                                        $beforeBalance = 0.00;
                                        if ($type->code === 'VL') {
                                            $beforeBalance = $leaveRequest->vl_balance_snapshot ?? ($indexedBalances->get($type->id)?->balance ?? 0.00);
                                        } elseif ($type->code === 'SL') {
                                            $beforeBalance = $leaveRequest->sl_balance_snapshot ?? ($indexedBalances->get($type->id)?->balance ?? 0.00);
                                        } elseif ($type->code === 'FL') {
                                            $beforeBalance = $leaveRequest->fl_balance_snapshot ?? ($indexedBalances->get($type->id)?->balance ?? 0.00);
                                        } elseif ($type->code === 'SPL') {
                                            $beforeBalance = $leaveRequest->spl_balance_snapshot ?? ($indexedBalances->get($type->id)?->balance ?? 0.00);
                                        }

                                        // 2. Resolve "After Request" mathematically based on approved transaction statuses
                                        $afterBalance = $beforeBalance;
                                        if ($leaveRequest->status === 'approved' && $leaveRequest->leave_type_id === $type->id) {
                                            $afterBalance = $beforeBalance - ($leaveRequest->days_with_pay ?? 0);
                                        }
                                    @endphp
                                    
                                    <div class="p-5 bg-white border border-gray-100/60 rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300">
                                        <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400 block mb-2">
                                            {{ $type->leave_type_name }} ({{ $type->code }})
                                        </span>
                                        
                                        @if($leaveRequest->status === 'pending')
                                            <!-- Simple layout for Pending Applications -->
                                            <div class="flex justify-between items-baseline mt-1">
                                                <span class="text-xs font-semibold text-gray-400">Available:</span>
                                                <span class="text-2xl font-black {{ $beforeBalance <= 0 ? 'text-rose-600' : 'text-gray-800' }}">
                                                    {{ number_format($beforeBalance, 2) }}
                                                </span>
                                            </div>
                                        @else
                                            <!-- Comprehensive Before vs After breakdown for Audited History logs -->
                                            <div class="space-y-1.5 pt-1">
                                                <div class="flex justify-between text-xs font-medium text-gray-400">
                                                    <span>Before Request:</span>
                                                    <span class="font-bold text-gray-700">{{ number_format($beforeBalance, 2) }}</span>
                                                </div>
                                                <div class="flex justify-between text-xs font-medium border-t border-gray-100 pt-1.5">
                                                    <span class="{{ $leaveRequest->status === 'approved' && $leaveRequest->leave_type_id === $type->id ? 'text-emerald-600 font-bold' : 'text-gray-400' }}">
                                                        After Action:
                                                    </span>
                                                    <span class="font-black text-sm {{ $afterBalance <= 0 ? 'text-rose-600' : ($leaveRequest->status === 'approved' && $leaveRequest->leave_type_id === $type->id ? 'text-emerald-600' : 'text-gray-800') }}">
                                                        {{ number_format($afterBalance, 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="pt-8 border-t border-gray-100/80">
                        
                        @if($leaveRequest->status === 'pending')
                            
                            @if(auth()->user()->is_admin)
                                <h3 class="text-xl font-extrabold text-gray-800 tracking-tight mb-6">Section 7: Details of Action on Application</h3>
                                
                                <form action="{{ route('admin.leave-requests.action', $leaveRequest->id) }}" method="POST" class="space-y-6">
                                    @csrf
                                    
                                    <input type="hidden" name="origin" value="{{ request()->routeIs('admin.*') ? 'masterlist' : 'dashboard' }}">
                                    
                                    <div>
                                        <label class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-3">Final Action Decision</label>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <label class="relative border-2 border-gray-100/80 rounded-2xl p-5 flex items-start space-x-4 cursor-pointer hover:bg-gray-50/50 transition-all duration-200 group has-[:checked]:border-gray-800 has-[:checked]:bg-gray-50/30">
                                                <div class="flex items-center h-5 mt-0.5">
                                                    <input type="radio" name="status" value="approved" id="action-approve" checked class="w-4 h-4 text-gray-800 border-gray-300 focus:ring-gray-800">
                                                </div>
                                                <div>
                                                    <span class="block font-extrabold text-gray-800 text-sm mb-1 group-has-[:checked]:text-gray-900">Approve Application</span>
                                                    <span class="block text-xs text-gray-500 font-medium">Deducts days from selected category matching input criteria.</span>
                                                </div>
                                            </label>
                                            
                                            <label class="relative border-2 border-gray-100/80 rounded-2xl p-5 flex items-start space-x-4 cursor-pointer hover:bg-rose-50/30 transition-all duration-200 group has-[:checked]:border-rose-500 has-[:checked]:bg-rose-50/50">
                                                <div class="flex items-center h-5 mt-0.5">
                                                    <input type="radio" name="status" value="disapproved" id="action-disapprove" class="w-4 h-4 text-rose-600 border-gray-300 focus:ring-rose-500">
                                                </div>
                                                <div>
                                                    <span class="block font-extrabold text-gray-800 text-sm mb-1 group-has-[:checked]:text-rose-700">Disapprove Application</span>
                                                    <span class="block text-xs text-gray-500 font-medium">Rejects application and leaves current balances untouched.</span>
                                                </div>
                                            </label>
                                        </div>
                                    </div>

                                    <div id="approval-inputs-panel" class="bg-gray-50/50 p-6 md:p-8 rounded-2xl border border-gray-100/60 space-y-6 transition-all duration-300">
                                        <h5 class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Approval Parameters Allocation</h5>
                                        
                                        @php
                                            // Safely calculate the recommended Paid vs Unpaid days based on the backend logic we built
                                            $autoDaysWithPay = $leaveRequest->details 
                                                ? $leaveRequest->details->where('is_with_pay', true)->sum('day_fraction') 
                                                : $leaveRequest->working_days_applied;
                                                
                                            $autoDaysWithoutPay = $leaveRequest->details 
                                                ? $leaveRequest->details->where('is_with_pay', false)->sum('day_fraction') 
                                                : 0;
                                        @endphp

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <label class="text-xs font-bold text-gray-700 block mb-2" for="days_with_pay">Days With Pay</label>
                                                <input type="number" step="0.5" min="0" name="days_with_pay" id="days_with_pay" 
                                                       value="{{ old('days_with_pay', $autoDaysWithPay) }}"
                                                       class="w-full rounded-xl border-gray-200/80 text-sm font-semibold focus:border-gray-800 focus:ring-gray-800 shadow-sm transition-colors">
                                            </div>
                                            <div>
                                                <label class="text-xs font-bold text-gray-700 block mb-2" for="days_without_pay">Days Without Pay</label>
                                                <input type="number" step="0.5" min="0" name="days_without_pay" id="days_without_pay" 
                                                       value="{{ old('days_without_pay', $autoDaysWithoutPay) }}"
                                                       class="w-full rounded-xl border-gray-200/80 text-sm font-semibold focus:border-gray-800 focus:ring-gray-800 shadow-sm transition-colors">
                                            </div>
                                        </div>

                                        <div>
                                            <label class="text-xs font-bold text-gray-700 block mb-2" for="recommendation_reason">Recommendation / Approval Remarks (Optional)</label>
                                            <textarea name="recommendation_reason" id="recommendation_reason" rows="2" 
                                                      placeholder="Provide explicit notes concerning approval details..."
                                                      class="w-full rounded-xl border-gray-200/80 text-sm font-medium focus:border-gray-800 focus:ring-gray-800 shadow-sm transition-colors">{{ old('recommendation_reason') }}</textarea>
                                        </div>
                                    </div>

                                    <div id="disapproval-inputs-panel" class="bg-rose-50/50 p-6 md:p-8 rounded-2xl border border-rose-100/60 space-y-4 hidden transition-all duration-300">
                                        <h5 class="text-[10px] font-bold uppercase tracking-wider text-rose-600">Disapproval Justification Required</h5>
                                        <div>
                                            <label class="text-xs font-bold text-gray-800 block mb-2" for="disapproval_reason">Reason for Disapproval</label>
                                            <textarea name="disapproval_reason" id="disapproval_reason" rows="3" 
                                                      placeholder="Specify clear legal/operational grounds for rejection as mandated by Civil Service guidelines..."
                                                      class="w-full rounded-xl border-rose-200/80 text-sm font-medium focus:border-rose-500 focus:ring-rose-500 shadow-sm transition-colors">{{ old('disapproval_reason') }}</textarea>
                                        </div>
                                    </div>

                                    <div class="pt-6 border-t border-gray-100 flex justify-end">
                                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-[#F2A455] hover:bg-[#df9344] text-white text-xs font-extrabold uppercase tracking-wider rounded-xl shadow-md shadow-orange-500/20 transition-all duration-200 active:scale-[0.98]">
                                            Commit Transaction Data
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="bg-amber-50/40 p-8 rounded-2xl border border-amber-100/60 text-center space-y-3">
                                    <div class="inline-flex p-3 bg-amber-100 text-amber-600 rounded-2xl shadow-inner mb-1">
                                        <svg class="w-6 h-6 animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-extrabold text-gray-800 tracking-tight">Application Processing Awaiting Review</h3>
                                    <p class="text-sm font-medium text-gray-500 max-w-md mx-auto">This request has been locked against updates and is currently awaiting formalized evaluation by your Department Head or approving officials.</p>
                                    <div class="pt-4 flex justify-center">
                                        @if(request()->query('from') === 'history')
                                            <a href="{{ route('leave-requests.history') }}" class="inline-flex items-center px-6 py-2.5 bg-white border border-gray-200/80 hover:bg-gray-50 text-gray-700 font-extrabold text-xs uppercase tracking-wider rounded-xl transition-all duration-200 shadow-sm active:scale-[0.98]">
                                                Back to History
                                            </a>
                                        @else
                                            <a href="{{ route('leave-requests.index') }}" class="inline-flex items-center px-6 py-2.5 bg-white border border-gray-200/80 hover:bg-gray-50 text-gray-700 font-extrabold text-xs uppercase tracking-wider rounded-xl transition-all duration-200 shadow-sm active:scale-[0.98]">
                                                Return to Dashboard
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        

                        @else
                            <div class="bg-gray-50/50 p-6 md:p-8 rounded-2xl border border-gray-100/60 space-y-6">
                                <h3 class="text-lg font-extrabold text-gray-800 tracking-tight flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    Historical Transaction Audit Trail Log
                                </h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="space-y-4">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1">Processing Action Status</span>
                                                <span class="font-extrabold capitalize {{ $leaveRequest->status === 'approved' ? 'text-emerald-600' : 'text-rose-600' }}">{{ $leaveRequest->status }}</span>
                                            </div>
                                            
                                            <div>
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1">Action Evaluated By</span>
                                                <span class="font-extrabold text-gray-800">
                                                    @if($leaveRequest->approvingOfficial)
                                                        {{ $leaveRequest->approvingOfficial->first_name }} {{ $leaveRequest->approvingOfficial->last_name }}
                                                    @else
                                                        <span class="text-gray-400 italic font-medium">Not Available</span>
                                                    @endif
                                                </span>
                                            </div>
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
                                        @else
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
                                    
                                    <!-- Bottom back panel blocks now respects 'from=history' source parameters properly -->
                                    @if(request()->routeIs('admin.*'))
                                        <a href="{{ route('admin.leave-requests.index') }}" class="inline-flex items-center px-6 py-2.5 bg-white border border-gray-200/80 hover:bg-gray-50 text-gray-700 font-extrabold text-xs uppercase tracking-wider rounded-xl transition-all duration-200 shadow-sm active:scale-[0.98]">
                                            Return to Masterlist
                                        </a>
                                    @elseif(request()->query('from') === 'history')
                                        <a href="{{ route('leave-requests.history') }}" class="inline-flex items-center px-6 py-2.5 bg-white border border-gray-200/80 hover:bg-gray-50 text-gray-700 font-extrabold text-xs uppercase tracking-wider rounded-xl transition-all duration-200 shadow-sm active:scale-[0.98]">
                                            Back to History
                                        </a>
                                    @else
                                        <a href="{{ route('leave-requests.index') }}" class="inline-flex items-center px-6 py-2.5 bg-white border border-gray-200/80 hover:bg-gray-50 text-gray-700 font-extrabold text-xs uppercase tracking-wider rounded-xl transition-all duration-200 shadow-sm active:scale-[0.98]">
                                            Return to Dashboard
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($leaveRequest->status === 'pending' && auth()->user()->is_admin)
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
    @endif
</x-app-layout>