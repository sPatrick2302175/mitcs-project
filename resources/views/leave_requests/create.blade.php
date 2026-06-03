<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Apply for Leave (Form No. 6)') }}
        </h2>
    </x-slot>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <style>
        /* Approved Company Leaves (Red) */
        .flatpickr-day.booked-by-other {
            background-color: #fee2e2 !important; 
            color: #b91c1c !important;            
            border-color: #fca5a5 !important;      
            font-weight: bold;
        }
        
        /* Pending Company Leaves (Yellow) */
        .flatpickr-day.pending-by-other {
            background-color: #fef08a !important; 
            color: #854d0e !important;            
            border-color: #fde047 !important;      
            font-weight: bold;
        }
    </style>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8">
                
                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4">
                        <div class="flex">
                            <div class="ml-3">
                                <p class="text-sm text-red-700 font-bold">
                                    Please fix the following errors before submitting:
                                </p>
                                <ul class="list-disc list-inside text-sm text-red-600 mt-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('leave-requests.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-10">
                        <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">6.A TYPE OF LEAVE TO BE AVAILED OF</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-3 gap-x-6">
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
                            @endphp

                            @foreach($leaveTypes as $type => $citation)
                                <label class="flex items-start space-x-2 cursor-pointer">
                                    <input type="radio" name="leave_type" value="{{ $type }}" @checked(old('leave_type') == $type) class="mt-1 rounded-full border-gray-300 text-indigo-600 shadow-sm" {{ $loop->first ? 'required' : '' }}>
                                    <span class="text-sm text-gray-700">{{ $type }} <span class="text-xs text-gray-500 block">{{ $citation }}</span></span>
                                </label>
                            @endforeach

                            <div class="col-span-1 md:col-span-2 mt-2">
                                <label class="flex items-center space-x-2 cursor-pointer mb-2">
                                    <input type="radio" name="leave_type" value="Others" @checked(old('leave_type') == 'Others') class="rounded-full border-gray-300 text-indigo-600 shadow-sm">
                                    <span class="text-sm text-gray-700 font-medium">Others:</span>
                                </label>
                                <input type="text" name="leave_type_others" value="{{ old('leave_type_others') }}" placeholder="Specify other leave type..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>
                    </div>

                    <div class="mb-10">
                        <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">6.B DETAILS OF LEAVE</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-6 rounded-lg border border-gray-200">
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm font-semibold text-gray-800 mb-2">In case of Vacation/Special Privilege Leave:</p>
                                    <label class="flex items-center space-x-2 mb-1">
                                        <input type="radio" name="leave_detail_category" value="Within the Philippines" @checked(old('leave_detail_category') == 'Within the Philippines') class="rounded-full border-gray-300 text-indigo-600 shadow-sm">
                                        <span class="text-sm text-gray-700">Within the Philippines</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" name="leave_detail_category" value="Abroad" @checked(old('leave_detail_category') == 'Abroad') class="rounded-full border-gray-300 text-indigo-600 shadow-sm">
                                        <span class="text-sm text-gray-700">Abroad</span>
                                    </label>
                                </div>

                                <div>
                                    <p class="text-sm font-semibold text-gray-800 mb-2">In case of Sick Leave:</p>
                                    <label class="flex items-center space-x-2 mb-1">
                                        <input type="radio" name="leave_detail_category" value="In Hospital" @checked(old('leave_detail_category') == 'In Hospital') class="rounded-full border-gray-300 text-indigo-600 shadow-sm">
                                        <span class="text-sm text-gray-700">In Hospital</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" name="leave_detail_category" value="Out Patient" @checked(old('leave_detail_category') == 'Out Patient') class="rounded-full border-gray-300 text-indigo-600 shadow-sm">
                                        <span class="text-sm text-gray-700">Out Patient</span>
                                    </label>
                                </div>

                                <div>
                                    <p class="text-sm font-semibold text-gray-800 mb-2">In case of Study Leave:</p>
                                    <label class="flex items-center space-x-2 mb-1">
                                        <input type="radio" name="leave_detail_category" value="Completion of Master's Degree" @checked(old('leave_detail_category') == "Completion of Master's Degree") class="rounded-full border-gray-300 text-indigo-600 shadow-sm">
                                        <span class="text-sm text-gray-700">Completion of Master's Degree</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" name="leave_detail_category" value="BAR/Board Examination Review" @checked(old('leave_detail_category') == 'BAR/Board Examination Review') class="rounded-full border-gray-300 text-indigo-600 shadow-sm">
                                        <span class="text-sm text-gray-700">BAR/Board Examination Review</span>
                                    </label>
                                </div>

                                <div>
                                    <p class="text-sm font-semibold text-gray-800 mb-2">Other purpose:</p>
                                    <label class="flex items-center space-x-2 mb-1">
                                        <input type="radio" name="leave_detail_category" value="Monetization of Leave Credits" @checked(old('leave_detail_category') == 'Monetization of Leave Credits') class="rounded-full border-gray-300 text-indigo-600 shadow-sm">
                                        <span class="text-sm text-gray-700">Monetization of Leave Credits</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" name="leave_detail_category" value="Terminal Leave" @checked(old('leave_detail_category') == 'Terminal Leave') class="rounded-full border-gray-300 text-indigo-600 shadow-sm">
                                        <span class="text-sm text-gray-700">Terminal Leave</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-800 mb-2">Specify Details (Illness / Location):</label>
                                <textarea name="leave_detail_specifics" rows="4" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Please provide location for Vacation Leave, or specific illness for Sick Leave...">{{ old('leave_detail_specifics') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="mb-8">
                        <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">6.C & 6.D DATES AND COMMUTATION</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Number of Working Days</label>
                                <input type="number" step="0.5" name="working_days_applied" value="{{ old('working_days_applied') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                @error('working_days_applied') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Inclusive Dates (Start)</label>
                                <input type="text" id="start_date" name="start_date" value="{{ old('start_date') }}" placeholder="YYYY-MM-DD" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                @error('start_date') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Inclusive Dates (End)</label>
                                <input type="text" id="end_date" name="end_date" value="{{ old('end_date') }}" placeholder="YYYY-MM-DD" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                @error('end_date') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 w-full md:w-1/2">
                            <label class="block text-sm font-semibold text-gray-800 mb-2">Commutation</label>
                            <div class="flex space-x-6">
                                <label class="flex items-center space-x-2">
                                    <input type="radio" name="commutation_requested" value="0" @checked(old('commutation_requested', '0') == '0') class="rounded-full border-gray-300 text-indigo-600 shadow-sm" required>
                                    <span class="text-sm text-gray-700">Not Requested</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="radio" name="commutation_requested" value="1" @checked(old('commutation_requested') == '1') class="rounded-full border-gray-300 text-indigo-600 shadow-sm">
                                    <span class="text-sm text-gray-700">Requested</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end border-t border-gray-200 pt-6 mt-6">
                        <a href="{{ route('leave-requests.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Cancel</a>
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Submit Application
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Retrieve mapped dates from Controller
            const disabledDates = @json($disabledDates ?? []);
            const companyApprovedDates = @json($companyApprovedDates ?? []);
            const companyPendingDates = @json($companyPendingDates ?? []);

            const commonConfig = {
                dateFormat: "Y-m-d",
                disable: disabledDates, // Disables both YOUR leaves and company APPROVED leaves
                minDate: "today",       // Disables past dates
                onDayCreate: function(dObj, dStr, fp, dayElem) {
                    const dateStr = fp.formatDate(dayElem.dateObj, "Y-m-d");
                    
                    // Style Company Approved Dates (Red)
                    if (companyApprovedDates.includes(dateStr)) {
                        dayElem.classList.add("booked-by-other");
                        dayElem.title = "Date taken by another approved employee";
                    }
                    // Style Company Pending Dates (Yellow)
                    else if (companyPendingDates.includes(dateStr)) {
                        dayElem.classList.add("pending-by-other");
                        dayElem.title = "Another employee has a pending request";
                    }
                }
            };

            const startPicker = flatpickr("#start_date", {
                ...commonConfig,
                onChange: function(selectedDates, dateStr, instance) {
                    endDatePicker.set('minDate', dateStr);
                }
            });

            const endDatePicker = flatpickr("#end_date", commonConfig);
        });
    </script>
</x-app-layout>