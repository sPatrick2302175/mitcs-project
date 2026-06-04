<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Edit Employee') }}: <span class="font-light">{{ $employee->first_name }} {{ $employee->last_name }}</span>
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Modernized Form Card Layer -->
            <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 p-8 md:p-10 transition-all duration-300">
                
                <!-- Modern, Soft Alert styling for Validation Errors -->
                @if ($errors->any())
                    <div class="mb-8 bg-rose-50/70 backdrop-blur-sm border border-rose-100 rounded-xl p-5 transition-all duration-300 animate-fadeIn">
                        <div class="flex items-start">
                            <div class="shrink-0">
                                <svg class="h-5 w-5 text-rose-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ms-3">
                                <h3 class="text-sm font-semibold text-rose-800">
                                    Please fix the following errors before submitting:
                                </h3>
                                <ul class="list-disc list-inside text-sm text-rose-600 mt-2 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('employees.update', $employee->id) }}" method="POST" class="space-y-8">
                    @csrf 
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        
                        <!-- First Name -->
                        <div class="group">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2 group-focus-within:text-[#F2A455] transition-colors duration-200">
                                First Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="first_name" value="{{ old('first_name', $employee->first_name) }}" required 
                                class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/10 focus:bg-white transition-all duration-200 sm:text-sm">
                        </div>

                        <!-- M.I. & Last Name Split Layout -->
                        <div class="flex gap-4">
                            <div class="w-1/4 group">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2 group-focus-within:text-[#F2A455] transition-colors duration-200">M.I.</label>
                                <input type="text" name="middle_initial" value="{{ old('middle_initial', $employee->middle_initial) }}" maxlength="5" 
                                    class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 text-center placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/10 focus:bg-white transition-all duration-200 sm:text-sm">
                            </div>
                            <div class="w-3/4 group">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2 group-focus-within:text-[#F2A455] transition-colors duration-200">
                                    Last Name <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="last_name" value="{{ old('last_name', $employee->last_name) }}" required 
                                    class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/10 focus:bg-white transition-all duration-200 sm:text-sm">
                            </div>
                        </div>

                        <!-- Position -->
                        <div class="md:col-span-2 group">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2 group-focus-within:text-[#F2A455] transition-colors duration-200">
                                Position <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="position" value="{{ old('position', $employee->position) }}" required 
                                class="block w-full md:w-1/2 rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/10 focus:bg-white transition-all duration-200 sm:text-sm">
                        </div>

                        <!-- Department Selection Dropdown -->
                        <div class="group">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2 group-focus-within:text-[#F2A455] transition-colors duration-200">
                                Department <span class="text-rose-500">*</span>
                            </label>
                            <select name="department_id" id="department_dropdown" required 
                                class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/10 focus:bg-white transition-all duration-200 sm:text-sm">
                                <option value="">-- Select Department --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" @selected(old('department_id', $employee->department_id) == $dept->id)>
                                        {{ $dept->department_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Dependent Division Dropdown -->
                        <div class="group">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2 group-focus-within:text-[#F2A455] transition-colors duration-200">
                                Division <span class="text-rose-500">*</span>
                            </label>
                            <select name="division_id" id="division_dropdown" required 
                                class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/10 focus:bg-white transition-all duration-200 sm:text-sm">
                                <option value="">-- Select Division --</option>
                                @foreach($divisions as $div)
                                    <option value="{{ $div->id }}" data-department="{{ $div->department_id }}" @selected(old('division_id', $employee->division_id) == $div->id)>
                                        {{ $div->division_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <!-- Enhanced Smooth Buttons Footer Context -->
                    <div class="flex items-center justify-between border-t border-gray-100 pt-6 mt-10">
                        
                        <!-- Delete Button (Left side) -->
                        <div>
                            <button type="button" onclick="if(confirm('Are you sure you want to permanently delete this employee? This action cannot be undone.')) document.getElementById('delete-form').submit();" 
                                class="inline-flex items-center px-4 py-3 bg-rose-50 border border-rose-100 rounded-xl font-bold text-xs text-rose-600 uppercase tracking-widest hover:bg-rose-500 hover:text-white focus:outline-none focus:ring-2 focus:ring-rose-500/40 active:scale-[0.98] transition-all duration-200 shadow-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                Delete Employee
                            </button>
                        </div>

                        <!-- Actions (Right side) -->
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('employees.show', $employee->id) }}" 
                                class="text-sm font-semibold text-gray-500 hover:text-gray-800 px-5 py-3 rounded-xl hover:bg-gray-50 transition-all duration-200">
                                Cancel
                            </a>
                            <button type="submit" 
                                class="inline-flex items-center px-6 py-3 bg-[#F2A455] border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-[#df9344] focus:outline-none focus:ring-2 focus:ring-[#F2A455]/40 active:scale-[0.98] transition-all duration-200 shadow-lg shadow-orange-500/10">
                                Update Employee
                            </button>
                        </div>
                    </div>
                </form>

                <form id="delete-form" action="{{ route('employees.destroy', $employee->id) }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>

            </div>
        </div>
    </div>

    <!-- Maintained Functional Vanilla JS with Clean UI Transition Logic Override -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deptDropdown = document.getElementById('department_dropdown');
            const divDropdown = document.getElementById('division_dropdown');
            
            const allDivOptions = Array.from(divDropdown.options).filter(opt => opt.value !== "");
            
            const targetDivId = "{{ old('division_id', $employee->division_id) }}";

            function filterDivisions() {
                const selectedDept = deptDropdown.value;

                divDropdown.innerHTML = '<option value="">-- Select Division --</option>';
                
                // Reset styling to active state
                divDropdown.classList.remove('bg-gray-100/60', 'text-gray-400', 'cursor-not-allowed');
                divDropdown.classList.add('bg-gray-50/40', 'text-gray-800');
                
                if (!selectedDept) {
                    divDropdown.innerHTML = '<option value="">-- Select Department First --</option>';
                    divDropdown.disabled = true;
                    // Apply disabled styling
                    divDropdown.classList.remove('bg-gray-50/40', 'text-gray-800');
                    divDropdown.classList.add('bg-gray-100/60', 'text-gray-400', 'cursor-not-allowed');
                    return;
                }

                divDropdown.disabled = false;
                let hasMatches = false;

                allDivOptions.forEach(option => {
                    if (option.getAttribute('data-department') === selectedDept) {
                        const newOption = option.cloneNode(true);
                        
                        if (newOption.value === targetDivId) {
                            newOption.selected = true;
                        }
                        
                        divDropdown.appendChild(newOption);
                        hasMatches = true;
                    }
                });

                if (!hasMatches) {
                    divDropdown.innerHTML = '<option value="">-- No Divisions in this Dept --</option>';
                    divDropdown.disabled = true;
                    // Apply disabled styling
                    divDropdown.classList.remove('bg-gray-50/40', 'text-gray-800');
                    divDropdown.classList.add('bg-gray-100/60', 'text-gray-400', 'cursor-not-allowed');
                }
            }

            // Run immediately on load
            filterDivisions();

            // Run on department change
            deptDropdown.addEventListener('change', filterDivisions);
        });
    </script>
</x-app-layout>