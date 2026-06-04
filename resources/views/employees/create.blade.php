<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Add New Employee') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 p-8 md:p-10 transition-all duration-300">
                
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

                <form action="{{ route('employees.store') }}" method="POST" class="space-y-8">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        
                        <div class="md:col-span-2 group">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2 group-focus-within:text-[#F2A455] transition-colors duration-200">
                                Employee ID Number <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="employee_id_number" value="{{ old('employee_id_number') }}" required 
                                class="block w-full md:w-1/2 rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/10 focus:bg-white transition-all duration-200 sm:text-sm">
                        </div>

                        <div class="group">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2 group-focus-within:text-[#F2A455] transition-colors duration-200">
                                First Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="first_name" value="{{ old('first_name') }}" required 
                                class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/10 focus:bg-white transition-all duration-200 sm:text-sm">
                        </div>

                        <div class="flex gap-4">
                            <div class="w-1/4 group">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2 group-focus-within:text-[#F2A455] transition-colors duration-200">M.I.</label>
                                <input type="text" name="middle_initial" value="{{ old('middle_initial') }}" maxlength="5" placeholder="A" 
                                    class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 text-center placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/10 focus:bg-white transition-all duration-200 sm:text-sm">
                            </div>
                            <div class="w-3/4 group">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2 group-focus-within:text-[#F2A455] transition-colors duration-200">
                                    Last Name <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="last_name" value="{{ old('last_name') }}" required 
                                    class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/10 focus:bg-white transition-all duration-200 sm:text-sm">
                            </div>
                        </div>

                        <div class="group">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2 group-focus-within:text-[#F2A455] transition-colors duration-200">
                                Position <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="position" value="{{ old('position') }}" required 
                                class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/10 focus:bg-white transition-all duration-200 sm:text-sm">
                        </div>

                        <div class="group">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2 group-focus-within:text-[#F2A455] transition-colors duration-200">Leave Credits (Temp)</label>
                            <input type="number" name="leave_credits" value="{{ old('leave_credits') }}" 
                                class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/10 focus:bg-white transition-all duration-200 sm:text-sm">
                        </div>

                        <div class="group">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2 group-focus-within:text-[#F2A455] transition-colors duration-200">
                                Department <span class="text-rose-500">*</span>
                            </label>
                            <select name="department_id" id="department_dropdown" required 
                                class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/10 focus:bg-white transition-all duration-200 sm:text-sm">
                                <option value="">-- Select Department --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>{{ $dept->department_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="group">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2 group-focus-within:text-[#F2A455] transition-colors duration-200">
                                Division <span class="text-rose-500">*</span>
                            </label>
                            <select name="division_id" id="division_dropdown" required disabled 
                                class="block w-full rounded-xl border-gray-200 bg-gray-100/60 px-4 py-3 text-gray-400 transition-all duration-200 sm:text-sm cursor-not-allowed">
                                <option value="">-- Select Department First --</option>
                                @foreach($divisions as $div)
                                    <option value="{{ $div->id }}" data-department="{{ $div->department_id }}">
                                        {{ $div->division_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="flex items-center justify-end border-t border-gray-100 pt-6 mt-10 space-x-3">
                        <a href="{{ route('employees.index') }}" 
                            class="text-sm font-semibold text-gray-500 hover:text-gray-800 px-5 py-3 rounded-xl hover:bg-gray-50 transition-all duration-200">
                            Cancel
                        </a>
                        <button type="submit" 
                            class="inline-flex items-center px-6 py-3 bg-[#F2A455] border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-[#df9344] focus:outline-none focus:ring-2 focus:ring-[#F2A455]/40 active:scale-[0.98] transition-all duration-200 shadow-lg shadow-orange-500/10">
                            Save Employee
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deptDropdown = document.getElementById('department_dropdown');
            const divDropdown = document.getElementById('division_dropdown');
            
            const allDivOptions = Array.from(divDropdown.options).filter(opt => opt.value !== "");
            
            const oldDeptId = "{{ old('department_id') }}";
            const oldDivId = "{{ old('division_id') }}";

            function updateDivisionDropdown(selectedDept) {
                divDropdown.innerHTML = '<option value="">-- Select Division --</option>';
                
                // Clear out locked/disabled styling layout components safely
                divDropdown.classList.remove('bg-gray-100/60', 'text-gray-400', 'cursor-not-allowed');
                divDropdown.classList.add('bg-gray-50/40', 'text-gray-800');
                
                if (!selectedDept) {
                    divDropdown.innerHTML = '<option value="">-- Select Department First --</option>';
                    divDropdown.disabled = true;
                    divDropdown.classList.remove('bg-gray-50/40', 'text-gray-800');
                    divDropdown.classList.add('bg-gray-100/60', 'text-gray-400', 'cursor-not-allowed');
                    return;
                }

                divDropdown.disabled = false;
                let hasMatches = false;

                allDivOptions.forEach(option => {
                    if (option.getAttribute('data-department') === selectedDept) {
                        const newOption = option.cloneNode(true);
                        if (oldDivId && newOption.value === oldDivId) {
                            newOption.selected = true;
                        }
                        divDropdown.appendChild(newOption);
                        hasMatches = true;
                    }
                });

                if (!hasMatches) {
                    divDropdown.innerHTML = '<option value="">-- No Divisions in this Dept --</option>';
                    divDropdown.disabled = true;
                    divDropdown.classList.remove('bg-gray-50/40', 'text-gray-800');
                    divDropdown.classList.add('bg-gray-100/60', 'text-gray-400', 'cursor-not-allowed');
                }
            }

            deptDropdown.addEventListener('change', function() {
                updateDivisionDropdown(this.value);
            });

            if (oldDeptId) {
                updateDivisionDropdown(oldDeptId);
            }
        });
    </script>
</x-app-layout>