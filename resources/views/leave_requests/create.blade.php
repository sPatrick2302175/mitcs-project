<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Apply for Leave (Form No. 6)') }}
        </h2>
    </x-slot>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <style>
        .flatpickr-calendar {
            font-family: inherit;
            border-radius: 1rem !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05) !important;
            border: 1px solid #f3f4f6 !important;
            padding: 0.25rem;
        }
        
        .flatpickr-day.selected {
            background: #F2A455 !important;
            border-color: #F2A455 !important;
            border-radius: 0.5rem !important;
        }

       /* Styling for Approved Leaves (Red/Taken) - OVERRIDE DISABLED STATE */
        .flatpickr-day.flatpickr-disabled.booked-by-other, 
        .flatpickr-day.flatpickr-disabled.booked-by-other:hover {
            background-color: #ffbcbc !important; 
            border-color: #ef4444 !important;
            color: #1d1d1d !important;
            border-radius: 0.5rem !important;
            font-weight: 700 !important;
            opacity: 0.65 !important;
            cursor: not-allowed;
        }

        /* Styling for MY Own Booked Leaves (Blue) - OVERRIDE DISABLED STATE */
        .flatpickr-day.flatpickr-disabled.my-booked-date, 
        .flatpickr-day.flatpickr-disabled.my-booked-date:hover {
            background-color: #c3d9fc !important; 
            border-color: #3b82f6 !important;
            color: #1d1d1d !important;
            border-radius: 0.5rem !important;
            font-weight: 700 !important;
            opacity: 0.8 !important;
            cursor: not-allowed;
        }

        /* Holiday Styling (Purple) - OVERRIDE DISABLED STATE */
        .flatpickr-day.flatpickr-disabled.holiday-date, 
        .flatpickr-day.flatpickr-disabled.holiday-date:hover {
            background-color: #e9d5ff !important; 
            border-color: #a855f7 !important;
            color: #1d1d1d !important;
            border-radius: 0.5rem !important;
            font-weight: 700 !important;
            opacity: 0.8 !important;
            cursor: not-allowed;
        }

        /* Standard Disabled Days (Weekends & Purely Non-working Days) */
        .flatpickr-day.flatpickr-disabled:not(.booked-by-other):not(.my-booked-date):not(.holiday-date) {
            opacity: 0.4 !important; /* Extremely light opacity */
            cursor: not-allowed !important;
        }
    </style>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-8 items-start">
                
                <div class="flex-1 w-full bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 p-8">
                
                <!-- Error Alert -->
                @if ($errors->any() || session('error'))
                    <div class="mb-6 bg-rose-50/70 backdrop-blur-sm border border-rose-100 rounded-2xl p-5 shadow-sm transition-all duration-300 animate-fadeIn flex items-start">
                        <div class="shrink-0 bg-rose-100 p-2 rounded-xl">
                            <svg class="h-5 w-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div class="ms-4 mt-0.5">
                            <h3 class="text-sm font-extrabold text-rose-800 tracking-tight">Please review the following errors:</h3>
                            <ul class="list-disc list-inside text-sm font-medium text-rose-700 mt-1.5 space-y-0.5">
                                @if(session('error'))
                                    <li>{{ session('error') }}</li>
                                @endif
                                
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif


                <form action="{{ route('leave-requests.store') }}" method="POST">
                    @csrf
                    
                    <!-- Section 6.A -->
                    <div class="mb-10">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-wider border-b border-gray-100 pb-3 mb-6">6.A TYPE OF LEAVE TO BE AVAILED OF</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @php
                                $leaveTypes = [
                                    'Vacation Leave' => '(Sec. 51, Rule XVI, Omnibus Rules Implementing E.O. No. 292)',
                                    'Mandatory/Forced Leave' => '(Sec. 25, Rule XVI, Omnibus Rules Implementing E.O. No. 292)',
                                    'Sick Leave' => '(Sec. 43, Rule XVI, Omnibus Rules Implementing E.O. No. 292)',
                                    'Maternity Leave' => '(R.A. No. 11210 / IRR Issued by CSC, DOLE and SSS)',
                                    'Paternity Leave' => '(R.A. No. 8187 / CSC MC No. 71, s. 1998, as amended)',
                                    'Special Privilege Leave' => '(Sec. 21, Rule XVI, Omnibus Rules Implementing E.O. No. 292)',
                                    'Solo Parent Leave' => '(RA No. 8972 / CSC MC No. 8, s. 2004)',
                                    'Study Leave' => '(Sec. 68, Rule XVI, Omnibus Rules Implementing E.O. No. 292)',
                                    '10-Day VAWC Leave' => '(RA No. 9262 / CSC MC No. 15, s. 2005)',
                                    'Rehabilitation Privilege' => '(Sec. 55, Rule XVI, Omnibus Rules Implementing E.O. No. 292)',
                                    'Special Leave Benefits for Women' => '(RA No. 9710 / CSC MC No. 25, s. 2010)',
                                    'Special Emergency Leave' => '(CSC MC No. 2, s. 2012, as amended)',
                                    'Adoption Leave' => '(R.A. No. 8552)'
                                ];

                                // 1. Fetch all actual leave types from the database
                                $dbLeaveTypes = \App\Models\LeaveType::all();
                            @endphp

                            @foreach($leaveTypes as $type => $citation)
                                @php
                                    // 1. Improved Matching Logic: Check for direct matches first before stripping words
                                    $dbType = $dbLeaveTypes->first(function($item) use ($type) {
                                        // Case-insensitive direct match
                                        if (stripos($item->leave_type_name, $type) !== false || stripos($type, $item->leave_type_name) !== false) {
                                            return true;
                                        }
                                        // Fallback for names like "Special Emergency (Calamity) Leave"
                                        $search = str_replace(' Leave', '', $type); 
                                        return stripos($item->leave_type_name, $search) !== false;
                                    });
                                    
                                    $typeId = $dbType ? $dbType->id : '';
                                @endphp

                                <label class="flex items-start p-4 bg-white border border-gray-200/80 rounded-xl cursor-pointer hover:border-orange-300 hover:bg-orange-50/10 focus-within:ring-2 focus-within:ring-[#F2A455]/40 transition group">
                                    <input type="radio" 
                                        name="leave_type_id" 
                                        value="{{ $typeId }}" 
                                        data-name="{{ $type }}"
                                        @checked(old('leave_type_id') !== null && old('leave_type_id') == $typeId) 
                                        class="mt-1 w-4 h-4 text-[#F2A455] border-gray-300 focus:ring-[#F2A455] focus:ring-offset-0 bg-gray-50 transition" 
                                        {{ $loop->first ? 'required' : '' }}>
                                    <div class="ms-3">
                                        <span class="text-sm font-bold text-gray-700 group-hover:text-gray-900 transition-colors block">
                                            {{ $type }}
                                        </span>
                                        <span class="text-xs font-medium text-gray-400 block mt-0.5 leading-relaxed">
                                            {{ $citation }}
                                        </span>
                                    </div>
                                </label>
                            @endforeach

                            <div class="col-span-1 md:col-span-2 mt-2 p-4 rounded-xl bg-gray-50/50 border border-gray-200/60 shadow-inner">
                                <label class="flex items-center space-x-3 cursor-pointer mb-2 group">
                                    <input type="radio" name="leave_type_id" value="others" data-name="Others" @checked(old('leave_type_id') === 'others') class="w-4 h-4 text-[#F2A455] border-gray-300 focus:ring-[#F2A455] focus:ring-offset-0 bg-gray-50 transition">
                                    <span class="text-sm font-bold text-gray-700 group-hover:text-gray-900 transition-colors">Others:</span>
                                </label>
                                <input type="text" name="leave_type_others" value="{{ old('leave_type_others') }}" placeholder="Specify other leave type..." class="block w-full rounded-xl border-gray-200 bg-white shadow-sm focus:border-[#F2A455] focus:ring-[#F2A455] text-sm font-medium py-2.5 transition-all">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Salary Input -->
                    <div class="mb-8 mt-6">
                        <div class="bg-gray-50/40 backdrop-blur-sm p-6 rounded-2xl border border-gray-100/80 w-full md:w-1/2 shadow-sm">
                            <label class="block text-xs font-extrabold text-gray-400 uppercase tracking-wider mb-2">
                                Monthly Salary <span class="text-rose-500">*</span>
                            </label>
                            <p class="text-[11px] font-medium text-gray-400 mb-4">Please verify or update your current basic monthly salary.</p>
                            
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-gray-500 font-black">₱</span>
                                </div>
                                <input type="number" step="0.01" name="salary" 
                                    value="{{ old('salary', auth()->user()->employee->salary ?? '') }}" required 
                                    class="block w-full pl-9 rounded-xl border-gray-200 bg-white shadow-sm focus:border-[#F2A455] focus:ring-[#F2A455] text-sm font-medium py-2.5 transition-all placeholder-gray-300" 
                                    placeholder="0.00">
                            </div>
                            @error('salary') <span class="text-xs font-bold text-rose-500 mt-2 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Section 6.B Container -->
                    <div id="section_6b_wrapper" class="transition-all duration-500 ease-in-out overflow-hidden max-h-0 opacity-0 pointer-events-none">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-wider border-b border-gray-100 pb-3 mb-6">6.B DETAILS OF LEAVE</h3>
                        
                        <div id="details_layout_container" class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50/40 backdrop-blur-sm p-6 rounded-2xl border border-gray-100/80 shadow-sm transition-all duration-500">
                            
                            <div id="sub_categories_left_col" class="transition-all duration-300 ease-in-out">
                                
                                <div id="details_vacation" class="transition-all duration-300 ease-in-out max-h-0 opacity-0 overflow-hidden px-1.5 py-0.5">
                                    <p class="text-xs font-extrabold text-gray-400 uppercase tracking-wider mb-3">In case of Vacation/Special Privilege Leave:</p>
                                    <div class="space-y-2">
                                        <label class="flex items-center space-x-3 cursor-pointer group p-2 rounded-lg hover:bg-white/60 transition shadow-sm border border-transparent hover:border-gray-100">
                                            <input type="radio" name="leave_detail_category" value="Within the Philippines" @checked(old('leave_detail_category') == 'Within the Philippines') class="w-4 h-4 text-[#F2A455] border-gray-300 focus:ring-[#F2A455] focus:ring-offset-0 bg-gray-50 transition">
                                            <span class="text-sm font-semibold text-gray-600 group-hover:text-gray-800 transition-colors">Within the Philippines</span>
                                        </label>
                                        <label class="flex items-center space-x-3 cursor-pointer group p-2 rounded-lg hover:bg-white/60 transition shadow-sm border border-transparent hover:border-gray-100">
                                            <input type="radio" name="leave_detail_category" value="Abroad" @checked(old('leave_detail_category') == 'Abroad') class="w-4 h-4 text-[#F2A455] border-gray-300 focus:ring-[#F2A455] focus:ring-offset-0 bg-gray-50 transition">
                                            <span class="text-sm font-semibold text-gray-600 group-hover:text-gray-800 transition-colors">Abroad</span>
                                        </label>
                                    </div>
                                </div>

                                <div id="details_sick" class="transition-all duration-300 ease-in-out max-h-0 opacity-0 overflow-hidden px-1.5 py-0.5">
                                    <p class="text-xs font-extrabold text-gray-400 uppercase tracking-wider mb-3">In case of Sick Leave:</p>
                                    <div class="space-y-2">
                                        <label class="flex items-center space-x-3 cursor-pointer group p-2 rounded-lg hover:bg-white/60 transition shadow-sm border border-transparent hover:border-gray-100">
                                            <input type="radio" name="leave_detail_category" value="In Hospital" @checked(old('leave_detail_category') == 'In Hospital') class="w-4 h-4 text-[#F2A455] border-gray-300 focus:ring-[#F2A455] focus:ring-offset-0 bg-gray-50 transition">
                                            <span class="text-sm font-semibold text-gray-600 group-hover:text-gray-800 transition-colors">In Hospital</span>
                                        </label>
                                        <label class="flex items-center space-x-3 cursor-pointer group p-2 rounded-lg hover:bg-white/60 transition shadow-sm border border-transparent hover:border-gray-100">
                                            <input type="radio" name="leave_detail_category" value="Out Patient" @checked(old('leave_detail_category') == 'Out Patient') class="w-4 h-4 text-[#F2A455] border-gray-300 focus:ring-[#F2A455] focus:ring-offset-0 bg-gray-50 transition">
                                            <span class="text-sm font-semibold text-gray-600 group-hover:text-gray-800 transition-colors">Out Patient</span>
                                        </label>
                                    </div>
                                </div>

                                <div id="details_study" class="transition-all duration-300 ease-in-out max-h-0 opacity-0 overflow-hidden px-1.5 py-0.5">
                                    <p class="text-xs font-extrabold text-gray-400 uppercase tracking-wider mb-3">In case of Study Leave:</p>
                                    <div class="space-y-2">
                                        <label class="flex items-center space-x-3 cursor-pointer group p-2 rounded-lg hover:bg-white/60 transition shadow-sm border border-transparent hover:border-gray-100">
                                            <input type="radio" name="leave_detail_category" value="Completion of Master's Degree" @checked(old('leave_detail_category') == "Completion of Master's Degree") class="w-4 h-4 text-[#F2A455] border-gray-300 focus:ring-[#F2A455] focus:ring-offset-0 bg-gray-50 transition">
                                            <span class="text-sm font-semibold text-gray-600 group-hover:text-gray-800 transition-colors">Completion of Master's Degree</span>
                                        </label>
                                        <label class="flex items-center space-x-3 cursor-pointer group p-2 rounded-lg hover:bg-white/60 transition shadow-sm border border-transparent hover:border-gray-100">
                                            <input type="radio" name="leave_detail_category" value="BAR/Board Examination Review" @checked(old('leave_detail_category') == 'BAR/Board Examination Review') class="w-4 h-4 text-[#F2A455] border-gray-300 focus:ring-[#F2A455] focus:ring-offset-0 bg-gray-50 transition">
                                            <span class="text-sm font-semibold text-gray-600 group-hover:text-gray-800 transition-colors">BAR/Board Examination Review</span>
                                        </label>
                                    </div>
                                </div>

                                <div id="details_others" class="transition-all duration-300 ease-in-out max-h-0 opacity-0 overflow-hidden px-1.5 py-0.5">
                                    <p class="text-xs font-extrabold text-gray-400 uppercase tracking-wider mb-3">Other purpose:</p>
                                    <div class="space-y-2">
                                        <label class="flex items-center space-x-3 cursor-pointer group p-2 rounded-lg hover:bg-white/60 transition shadow-sm border border-transparent hover:border-gray-100">
                                            <input type="radio" name="leave_detail_category" value="Monetization of Leave Credits" @checked(old('leave_detail_category') == 'Monetization of Leave Credits') class="w-4 h-4 text-[#F2A455] border-gray-300 focus:ring-[#F2A455] focus:ring-offset-0 bg-gray-50 transition">
                                            <span class="text-sm font-semibold text-gray-600 group-hover:text-gray-800 transition-colors">Monetization of Leave Credits</span>
                                        </label>
                                        <label class="flex items-center space-x-3 cursor-pointer group p-2 rounded-lg hover:bg-white/60 transition shadow-sm border border-transparent hover:border-gray-100">
                                            <input type="radio" name="leave_detail_category" value="Terminal Leave" @checked(old('leave_detail_category') == 'Terminal Leave') class="w-4 h-4 text-[#F2A455] border-gray-300 focus:ring-[#F2A455] focus:ring-offset-0 bg-gray-50 transition">
                                            <span class="text-sm font-semibold text-gray-600 group-hover:text-gray-800 transition-colors">Terminal Leave</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div id="specifics_container" class="transition-all duration-300 ease-in-out max-h-0 opacity-0 overflow-hidden w-full">
                                <label id="specifics_label" class="block text-xs font-extrabold text-gray-400 uppercase tracking-wider mb-2">Specify Details:</label>
                                <textarea id="specifics_input" name="leave_detail_specifics" rows="4" class="block w-full rounded-xl border-gray-200 bg-white shadow-sm focus:border-[#F2A455] focus:ring-[#F2A455] text-sm font-medium p-3 transition-all placeholder-gray-400" placeholder="">{{ old('leave_detail_specifics') }}</textarea>
                            </div>
                        </div>

                        <div class="mb-2 mt-8">
                            <h3 class="text-xs font-black text-gray-400 uppercase tracking-wider border-b border-gray-100 pb-3 mb-6">SUPPORTING DOCUMENTS (OPTIONAL)</h3>
                            <div class="bg-gray-50/40 backdrop-blur-sm p-6 rounded-2xl border border-gray-100/80 w-full shadow-sm">
                                <label class="block text-xs font-extrabold text-gray-400 uppercase tracking-wider mb-2">Upload Attachments</label>
                                <p class="text-[11px] font-medium text-gray-400 mb-4">Attach medical certificates, letter requests, or other supporting documents if required for your leave type.</p>
                                
                                <div class="flex items-center justify-center w-full">
                                    <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-200 border-dashed rounded-xl cursor-pointer bg-white hover:bg-orange-50/30 hover:border-[#F2A455]/50 transition-all group">
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                            <svg class="w-8 h-8 mb-3 text-gray-300 group-hover:text-[#F2A455] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                            </svg>
                                            <p class="mb-1 text-sm text-gray-500 group-hover:text-gray-700"><span class="font-bold">Click to upload</span> or drag and drop</p>
                                            <p class="text-[10px] uppercase font-bold tracking-wider text-gray-400">PDF, JPG, PNG, or DOCX (Max: 5MB)</p>
                                        </div>
                                        <input id="dropzone-file" type="file" name="attachments[]" multiple class="hidden" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" />
                                    </label>
                                </div>
                                
                                <div id="file-list" class="mt-4 space-y-2 hidden"></div>
                                
                                @error('attachments') <span class="text-xs font-bold text-rose-500 mt-2 block">{{ $message }}</span> @enderror
                                @error('attachments.*') <span class="text-xs font-bold text-rose-500 mt-2 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                    </div>

                    <!-- Section 6.C & 6.D -->
                    <div class="mb-8">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-wider border-b border-gray-100 pb-3 mb-6">6.C & 6.D DATES AND COMMUTATION</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            
                            <div>
                                <label class="block text-xs font-extrabold text-gray-400 uppercase tracking-wider mb-2">Number of Working Days</label>
                                <input type="number" id="working_days_applied" step="0.5" name="working_days_applied" value="{{ old('working_days_applied') }}" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-[#F2A455] focus:ring-[#F2A455] text-sm font-semibold py-2.5 transition-all bg-gray-50 cursor-not-allowed" readonly required>
                                @error('working_days_applied') <span class="text-xs font-bold text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1 md:col-span-2">
                                <label class="block text-xs font-extrabold text-gray-400 uppercase tracking-wider mb-2">Selected Leave Dates</label>
                                <input type="text" id="selected_dates" name="selected_dates" value="{{ old('selected_dates') }}" placeholder="Click to select one or multiple dates..." class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-[#F2A455] focus:ring-[#F2A455] text-sm font-semibold py-2.5 transition-all bg-white" required readonly>
                                @error('selected_dates') <span class="text-xs font-bold text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                
                                <!-- Calendar Legend Keys Alignment -->
                                <div class="mt-3 flex flex-wrap gap-x-4 gap-y-2 items-center text-[11px] font-bold text-gray-400 uppercase tracking-wider bg-white border border-gray-100 p-2.5 rounded-xl shadow-sm">
                                    <span class="text-gray-500">Calendar Key:</span>
                                    <span class="inline-flex items-center gap-1.5 text-gray-600"><span class="w-3 h-3 rounded-md bg-[#c3d9fc] border border-[#3b82f6]"></span> My Requests</span>
                                    <span class="inline-flex items-center gap-1.5 text-gray-600"><span class="w-3 h-3 rounded-md bg-[#ffbcbc] border border-[#ef4444]"></span> Taken Leave</span>
                                    <span class="inline-flex items-center gap-1.5 text-gray-600"><span class="w-3 h-3 rounded-md bg-[#ffecca] border border-[#f59e0b]"></span> Holidays </span>
                                </div>
                                <p class="text-xs font-medium text-gray-400 mt-2">You can select non-consecutive dates. Weekends and company approved dates are omitted automatically.</p>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50/40 backdrop-blur-sm p-4 rounded-2xl border border-gray-100/80 w-full md:w-1/2 shadow-sm">
                            <label class="block text-xs font-extrabold text-gray-400 uppercase tracking-wider mb-2">Commutation</label>
                            <div class="flex space-x-6">
                                <label class="flex items-center space-x-3 cursor-pointer group">
                                    <input type="radio" name="commutation_requested" value="0" @checked(old('commutation_requested', '0') == '0') class="w-4 h-4 text-[#F2A455] border-gray-300 focus:ring-[#F2A455] focus:ring-offset-0 bg-gray-50 transition" required>
                                    <span class="text-sm font-semibold text-gray-600 group-hover:text-gray-800 transition-colors">Not Requested</span>
                                </label>
                                <label class="flex items-center space-x-3 cursor-pointer group">
                                    <input type="radio" name="commutation_requested" value="1" @checked(old('commutation_requested') == '1') class="w-4 h-4 text-[#F2A455] border-gray-300 focus:ring-[#F2A455] focus:ring-offset-0 bg-gray-50 transition">
                                    <span class="text-sm font-semibold text-gray-600 group-hover:text-gray-800 transition-colors">Requested</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Navigation -->
                    <div class="flex items-center justify-end border-t border-gray-100 pt-6 mt-6 space-x-4">
                        <a href="{{ route('leave-requests.index') }}" class="text-xs font-extrabold uppercase tracking-wider text-gray-400 hover:text-gray-600 transition-colors">Cancel</a>
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-[#F2A455] hover:bg-[#df9344] text-white text-xs font-extrabold uppercase tracking-wider rounded-xl shadow-md shadow-orange-500/10 transition-all duration-200 active:scale-[0.98]">
                            Submit Application
                        </button>
                    </div>
                </form>
            </div> 
            @if($employee = auth()->user()->employee)
                @php
                    // 1. Index the balances by leave_type_id for instant lookup
                    $indexedBalances = $employee->leaveBalances->keyBy('leave_type_id');
                    
                    // 2. Define exactly which leaves we want to show in this sidebar, in this order
                    $displayCodes = ['VL', 'SL', 'FL', 'SPL', 'SEL'];
                    
                    // 3. Fetch them from the database and keep our preferred order
                    $leaveTypes = \App\Models\LeaveType::whereIn('code', $displayCodes)
                        ->get()
                        ->sortBy(function($model) use ($displayCodes) {
                            return array_search($model->code, $displayCodes);
                        });
                @endphp

                <div class="w-full lg:w-80 shrink-0 sticky top-8">
                    <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 p-8">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 border-b border-gray-100 pb-4 mb-6">Leave Balances</h3>
                        
                        <ul class="space-y-4">
                            @foreach($leaveTypes as $type)
                                @php
                                    // Safely get the balance or default to 0.00
                                    $balanceRecord = $indexedBalances->get($type->id);
                                    $balanceAmt = $balanceRecord ? (float)$balanceRecord->balance : 0.00;
                                    
                                    // Check if it should use the orange highlight (VL and SL) or gray (the rest)
                                    $isPrimary = in_array($type->code, ['VL', 'SL']);
                                @endphp
                                
                                <li class="flex justify-between items-center bg-gray-50/50 p-3 rounded-xl border border-gray-100/40">
                                    <span class="text-sm text-gray-600 font-semibold">
                                        {{ $type->code === 'SEL' ? 'Special Emergency' : str_replace(' Leave', '', $type->leave_type_name) }} Leave
                                    </span>
                                    
                                    @if($isPrimary)
                                        <span class="bg-orange-50 text-[#df9344] font-bold text-sm py-1 px-3 rounded-xl border border-orange-100/60">
                                            {{ number_format($balanceAmt, 2) }}
                                        </span>
                                    @else
                                        <span class="bg-gray-50 text-gray-700 font-bold text-sm py-1 px-3 rounded-xl border border-gray-200/40">
                                            {{ number_format($balanceAmt, 2) }}
                                        </span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            </div> 
        </div> 
    </div> 
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            /* -------------------------------------------------------------
                Dynamic Form 6.B Visibility Logic (Smooth Transition Mode)
            ------------------------------------------------------------- */
            const typeRadios = document.querySelectorAll('input[name="leave_type_id"]');
            const wrapper6B = document.getElementById('section_6b_wrapper');
            const detailsLayoutContainer = document.getElementById('details_layout_container');
            const subCategoriesLeftCol = document.getElementById('sub_categories_left_col');
            
            const subBlocks = {
                'Vacation Leave': document.getElementById('details_vacation'),
                'Special Privilege Leave': document.getElementById('details_vacation'),
                'Sick Leave': document.getElementById('details_sick'),
                'Study Leave': document.getElementById('details_study'),
                'Others': document.getElementById('details_others')
            };
            
            const specificsContainer = document.getElementById('specifics_container');
            const specificsLabel = document.getElementById('specifics_label');
            const specificsInput = document.getElementById('specifics_input');

            function showElement(el, maxPercentHeight = 'max-h-[500px]') {
                el.classList.remove('max-h-0', 'opacity-0');
                el.classList.add(maxPercentHeight, 'opacity-100');
            }

            function hideElement(el, maxPercentHeight = 'max-h-[500px]') {
                el.classList.remove(maxPercentHeight, 'opacity-100');
                el.classList.add('max-h-0', 'opacity-0');
            }

            function handleLeaveTypeChange() {
                const checkedRadio = document.querySelector('input[name="leave_type_id"]:checked');
                const selectedType = checkedRadio ? checkedRadio.dataset.name : null;
                
                // Reset layout structure modifications completely
                subCategoriesLeftCol.classList.remove('hidden');
                specificsContainer.classList.remove('max-w-xl', 'mx-auto');
                detailsLayoutContainer.className = "grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50/40 backdrop-blur-sm p-6 rounded-2xl border border-gray-100/80 shadow-sm mb-10 transition-all duration-500";

                // Hide all inside elements cleanly
                Object.values(subBlocks).forEach(block => hideElement(block));
                hideElement(specificsContainer);

                const typesWithSubOptions = ['Vacation Leave', 'Special Privilege Leave', 'Sick Leave', 'Study Leave', 'Others', 'Special Leave Benefits for Women'];
                
                if (typesWithSubOptions.includes(selectedType)) {
                    // Activate master wrapper smoothly
                    wrapper6B.classList.remove('max-h-0', 'opacity-0', 'pointer-events-none');
                    wrapper6B.classList.add('max-h-[1000px]', 'opacity-100', 'pointer-events-auto');

                    if (['Vacation Leave', 'Special Privilege Leave'].includes(selectedType)) {
                        showElement(subBlocks[selectedType]);
                        showElement(specificsContainer);
                        specificsLabel.textContent = 'Specify Destination (If Abroad):';
                        specificsInput.placeholder = 'Please provide destination...';
                    } 
                    else if (selectedType === 'Sick Leave') {
                        showElement(subBlocks['Sick Leave']);
                        showElement(specificsContainer);
                        specificsLabel.textContent = 'Specify Illness:';
                        specificsInput.placeholder = 'Please describe specific illness...';
                    }
                    else if (selectedType === 'Special Leave Benefits for Women') {
                        // 1. Hide left sub-options panel completely to clear layout space
                        subCategoriesLeftCol.classList.add('hidden');
                        
                        // 2. Reposition layout context into a clean, unified structure
                        detailsLayoutContainer.className = "flex flex-col items-center justify-center bg-gray-50/40 backdrop-blur-sm p-6 rounded-2xl border border-gray-100/80 shadow-sm mb-10 transition-all duration-500";
                        
                        // 3. Scale text box area smoothly with elegant maximum constraints
                        specificsContainer.classList.add('max-w-xl', 'mx-auto');
                        showElement(specificsContainer);
                        
                        specificsLabel.textContent = 'Specify Illness (Special Leave for Women):';
                        specificsInput.placeholder = 'Please describe specific illness...';
                    }
                    else if (selectedType === 'Study Leave' || selectedType === 'Others') {
                        showElement(subBlocks[selectedType]);
                    }
                } else {
                    // Collapse master envelope safely if type needs no details
                    wrapper6B.classList.remove('max-h-[1000px]', 'opacity-100', 'pointer-events-auto');
                    wrapper6B.classList.add('max-h-0', 'opacity-0', 'pointer-events-none');
                }
            }

            typeRadios.forEach(radio => {
                radio.addEventListener('change', handleLeaveTypeChange);
            });

            handleLeaveTypeChange();

            /* -------------------------------------------------------------
                File Upload Preview Logic
            ------------------------------------------------------------- */
            const fileInput = document.getElementById('dropzone-file');
            const fileListContainer = document.getElementById('file-list');

            if (fileInput) {
                fileInput.addEventListener('change', function() {
                    fileListContainer.innerHTML = ''; // Clear previous
                    
                    if (this.files.length > 0) {
                        fileListContainer.classList.remove('hidden');
                        
                        Array.from(this.files).forEach((file, index) => {
                            const fileSize = (file.size / (1024 * 1024)).toFixed(2); // Convert to MB
                            
                            const fileItem = document.createElement('div');
                            fileItem.className = 'flex items-center justify-between p-3 bg-white border border-gray-100 rounded-lg shadow-sm';
                            fileItem.innerHTML = `
                                <div class="flex items-center space-x-3 overflow-hidden">
                                    <div class="p-2 bg-orange-50 rounded-lg shrink-0">
                                        <svg class="w-4 h-4 text-[#F2A455]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                    </div>
                                    <div class="truncate">
                                        <p class="text-sm font-bold text-gray-700 truncate">${file.name}</p>
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">${fileSize} MB</p>
                                    </div>
                                </div>
                            `;
                            fileListContainer.appendChild(fileItem);
                        });
                    } else {
                        fileListContainer.classList.add('hidden');
                    }
                });
            }

            /* -------------------------------------------------------------
                Flatpickr Logic
            ------------------------------------------------------------- */
            // 1. Fetch raw holidays and clean the timestamps off the dates
            const rawHolidays = @json($holidays ?? []); 
            const holidays = {};
            for (const [key, value] of Object.entries(rawHolidays)) {
                // This chops "2026-06-12 00:00:00" down to just "2026-06-12"
                holidays[String(key).substring(0, 10)] = value;
            }
            const holidayDates = Object.keys(holidays);

            // 2. Clean the timestamps off the division and personal leaves
            const divisionApprovedDates = @json($divisionApprovedDates ?? []).map(d => String(d).substring(0, 10));
            const myBookedDates = @json($myBookedDates ?? []).map(d => String(d).substring(0, 10));
            
            // 3. Combine all explicitly blocked dates
            const explicitDisabledDates = [...divisionApprovedDates, ...myBookedDates, ...holidayDates];

            const commonConfig = {
                dateFormat: "Y-m-d",
                minDate: "today",       
                disable: [
                    function(date) {
                        // Disable Weekends entirely
                        if (date.getDay() === 0 || date.getDay() === 6) {
                            return true; 
                        }
                        
                        // Disable if it matches taken leaves, my requests, or holidays
                        const dateStr = flatpickr.formatDate(date, "Y-m-d");
                        return explicitDisabledDates.includes(dateStr);
                    }
                ],
                onDayCreate: function(dObj, dStr, fp, dayElem) {
                    const dateStr = fp.formatDate(dayElem.dateObj, "Y-m-d");
                    
                    // Prioritize color application (My request > Division Approved > Holiday)
                    if (myBookedDates.includes(dateStr)) {
                        dayElem.classList.add("my-booked-date");
                        dayElem.title = "My Leave Request";
                    }
                    else if (divisionApprovedDates.includes(dateStr)) {
                        dayElem.classList.add("booked-by-other");
                        dayElem.title = "Taken Leave";
                    }
                    else if (holidays[dateStr]) {
                        dayElem.classList.add("holiday-date");
                        dayElem.title = holidays[dateStr]; // Instantly prints the holiday name!
                    }
                }
            };

            flatpickr("#selected_dates", {
                ...commonConfig,
                mode: "multiple",
                conjunction: ", ",
                onChange: function(selectedDates, dateStr, instance) {
                    const workingDays = selectedDates.filter(date => {
                        const day = date.getDay();
                        return day !== 0 && day !== 6; 
                    });
                    document.getElementById('working_days_applied').value = workingDays.length;
                }
            });
        });
    </script>
</x-app-layout>