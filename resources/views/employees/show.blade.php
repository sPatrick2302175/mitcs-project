<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Employee Profile') }}: <span class="font-light">{{ $employee->first_name }} {{ $employee->last_name }}</span>
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
                
                <div class="md:col-span-2 bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 p-8">
                    
                    <div class="flex justify-between items-center border-b border-gray-100 pb-4 mb-6">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Basic Information</h3>
                        
                        @if(auth()->user()->is_admin === App\Models\User::ROLE_SUPER_ADMIN)
                            {{-- Modern Custom Dropdown for Super Admin --}}
                            <form action="{{ route('employees.changeRole', $employee->id) }}" method="POST" class="relative inline-block custom-dropdown">
                                @csrf
                                @method('PUT')

                                @php
                                    // Dynamically style the button based on the current role
                                    $currentRole = $employee->user ? $employee->user->is_admin : 0;
                                    $roleText = 'Employee';
                                    $btnClass = 'text-gray-700 bg-gray-50 border-gray-200 hover:bg-gray-100 focus:ring-gray-400';

                                    if ($currentRole == App\Models\User::ROLE_DEPT_HEAD) {
                                        $roleText = 'Dept Head';
                                        $btnClass = 'text-purple-700 bg-purple-50 border-purple-200 hover:bg-purple-100 focus:ring-purple-400';
                                    } elseif ($currentRole == App\Models\User::ROLE_ADMIN_OFFICER) {
                                        $roleText = 'Admin Officer';
                                        $btnClass = 'text-blue-700 bg-blue-50 border-blue-200 hover:bg-blue-100 focus:ring-blue-400';
                                    }
                                @endphp

                                <input type="hidden" name="role" value="{{ $currentRole }}">

                                <button type="button" onclick="toggleDropdown(this)" class="flex items-center justify-between w-36 px-3 py-1.5 text-xs font-semibold border rounded-md shadow-sm focus:outline-none focus:ring-2 transition-colors duration-200 {{ $btnClass }}">
                                    <span>{{ $roleText }}</span>
                                    <svg class="w-4 h-4 ml-2 transition-transform duration-300 transform dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>

                                <div class="absolute right-0 z-50 w-36 mt-2 origin-top-right bg-white border border-gray-100 rounded-md shadow-lg opacity-0 invisible transform scale-95 transition-all duration-200 ease-out dropdown-menu">
                                    <div class="p-1 space-y-1">
                                        <button type="button" onclick="submitRole(this, 0)" class="block w-full px-3 py-2 text-xs font-medium text-left text-gray-700 rounded-md hover:bg-gray-100 transition-colors">
                                            Employee
                                        </button>
                                        <button type="button" onclick="submitRole(this, {{ App\Models\User::ROLE_DEPT_HEAD }})" class="block w-full px-3 py-2 text-xs font-medium text-left text-purple-700 rounded-md hover:bg-purple-50 transition-colors">
                                            Dept Head
                                        </button>
                                        <button type="button" onclick="submitRole(this, {{ App\Models\User::ROLE_ADMIN_OFFICER }})" class="block w-full px-3 py-2 text-xs font-medium text-left text-blue-700 rounded-md hover:bg-blue-50 transition-colors">
                                            Admin Officer
                                        </button>
                                    </div>
                                </div>
                            </form>

                        @elseif(auth()->user()->is_admin === App\Models\User::ROLE_DEPT_HEAD)
                            {{-- Modern Custom Dropdown for Dept Head --}}
                            <form action="{{ route('employees.changeRole', $employee->id) }}" method="POST" class="relative inline-block custom-dropdown">
                                @csrf
                                @method('PUT')

                                @php
                                    $currentRole = $employee->user ? $employee->user->is_admin : 0;
                                    $roleText = $currentRole == App\Models\User::ROLE_ADMIN_OFFICER ? 'Admin Officer' : 'Employee';
                                    $btnClass = $currentRole == App\Models\User::ROLE_ADMIN_OFFICER
                                        ? 'text-blue-700 bg-blue-50 border-blue-200 hover:bg-blue-100 focus:ring-blue-400'
                                        : 'text-gray-700 bg-gray-50 border-gray-200 hover:bg-gray-100 focus:ring-gray-400';
                                @endphp

                                <input type="hidden" name="role" value="{{ $currentRole }}">

                                <button type="button" onclick="toggleDropdown(this)" class="flex items-center justify-between w-36 px-3 py-1.5 text-xs font-semibold border rounded-md shadow-sm focus:outline-none focus:ring-2 transition-colors duration-200 {{ $btnClass }}">
                                    <span>{{ $roleText }}</span>
                                    <svg class="w-4 h-4 ml-2 transition-transform duration-300 transform dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>

                                <div class="absolute right-0 z-50 w-36 mt-2 origin-top-right bg-white border border-gray-100 rounded-md shadow-lg opacity-0 invisible transform scale-95 transition-all duration-200 ease-out dropdown-menu">
                                    <div class="p-1 space-y-1">
                                        <button type="button" onclick="submitRole(this, 0)" class="block w-full px-3 py-2 text-xs font-medium text-left text-gray-700 rounded-md hover:bg-gray-100 transition-colors">
                                            Employee
                                        </button>
                                        <button type="button" onclick="submitRole(this, {{ App\Models\User::ROLE_ADMIN_OFFICER }})" class="block w-full px-3 py-2 text-xs font-medium text-left text-blue-700 rounded-md hover:bg-blue-50 transition-colors">
                                            Admin Officer
                                        </button>
                                    </div>
                                </div>
                            </form>

                        @elseif(auth()->user()->is_admin === App\Models\User::ROLE_ADMIN_OFFICER)
                            {{-- Admin Officer View --}}
                    
                        @endif
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6">
                        <div class="bg-gray-50/40 p-4 rounded-xl border border-gray-100/40">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">ID Number</p>
                            <p class="font-bold text-gray-800 sm:text-base tracking-wide">{{ $employee->employee_id_number }}</p>
                        </div>
                        
                        <div class="bg-gray-50/40 p-4 rounded-xl border border-gray-100/40">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Full Name</p>
                            <p class="font-bold text-gray-800 sm:text-base">
                                {{ $employee->first_name }} {{ $employee->middle_initial }} {{ $employee->last_name }}
                            </p>
                        </div>
                        
                        <div class="bg-gray-50/40 p-4 rounded-xl border border-gray-100/40">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Position</p>
                            <p class="font-bold text-gray-800 sm:text-base">{{ $employee->position }}</p>
                        </div>

                        <div class="bg-gray-50/40 p-4 rounded-xl border border-gray-100/40">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Position Code</p>
                            <p class="font-bold text-gray-800 sm:text-base">{{ $employee->position_code ?? 'N/A' }}</p>
                        </div>
                        
                        <div class="bg-gray-50/40 p-4 rounded-xl border border-gray-100/40 flex flex-col justify-center">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">System Status</p>
                            <div>
                                @if($employee->user)
                                    <span class="bg-emerald-50 text-emerald-700 border border-emerald-100/80 text-xs px-3 py-1.5 rounded-xl font-bold inline-flex items-center">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 me-2 animate-pulse"></span>
                                        Registered
                                    </span>
                                @else
                                    <span class="bg-gray-50 text-gray-500 border border-gray-200/60 text-xs px-3 py-1.5 rounded-xl font-medium inline-flex items-center">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 me-2"></span>
                                        Unregistered
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="bg-gray-50/40 p-4 rounded-xl border border-gray-100/40">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Department</p>
                            <p class="font-bold text-gray-800 sm:text-base">{{ $employee->department->department_name ?? 'N/A' }}</p>
                        </div>
                        
                        <div class="bg-gray-50/40 p-4 rounded-xl border border-gray-100/40">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Division</p>
                            <p class="font-bold text-gray-800 sm:text-base">{{ $employee->division->division_name ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 p-8 border-t-4 border-[#F2A455]">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 border-b border-gray-100 pb-4 mb-6">Leave Balances</h3>
                    
                    @php
                        // 1. The 4 main leaves we want to display, exactly in this order
                        $displayCodes = ['VL', 'SL', 'FL', 'SPL'];
                        
                        // 2. Index the employee's balances by leave_type_id for fast, safe lookup
                        $indexedBalances = $employee->leaveBalances->keyBy('leave_type_id');
                        
                        // 3. Fetch ONLY the four designated leave types from the database
                        $filteredLeaves = \App\Models\LeaveType::whereIn('code', $displayCodes)
                            ->get()
                            ->sortBy(function($model) use ($displayCodes) {
                                return array_search($model->code, $displayCodes);
                            });
                    @endphp

                    <ul class="space-y-4">
                        @forelse($filteredLeaves as $leaveType)
                            @php
                                // Safely get the balance amount or default to 0.00
                                $balanceRecord = $indexedBalances->get($leaveType->id);
                                $balanceAmt = $balanceRecord ? (float)$balanceRecord->balance : 0.00;
                                
                                // Keep the UI consistent: VL/SL are orange, FL/SPL are gray
                                $isPrimary = in_array($leaveType->code, ['VL', 'SL']);
                                
                                // Safely handle the naming (using leave_type_name or fallback to name)
                                $leaveName = $leaveType->leave_type_name ?? $leaveType->name;
                            @endphp
                            
                            <li class="flex justify-between items-center bg-gray-50/50 p-3 rounded-xl border border-gray-100/40">
                                <span class="text-sm text-gray-600 font-semibold">
                                    {{ str_replace(' Leave', '', $leaveName) }} Leave
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
                        @empty
                            <li class="text-center text-sm text-gray-400 py-4 italic">
                                No main leave balances available.
                            </li>
                        @endforelse
                    </ul>
                </div>

            </div>
            
            <div class="mt-8 flex justify-end items-center space-x-3">
                <a href="{{ route('employees.index') }}" 
                    class="text-sm font-semibold text-gray-500 hover:text-gray-800 px-5 py-3 rounded-xl hover:bg-gray-100/60 transition-all duration-200">
                    Back to Employee List
                </a>
                <a href="{{ route('employees.edit', $employee->id) }}" 
                    class="inline-flex items-center px-6 py-3 bg-[#F2A455] border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-[#df9344] focus:outline-none focus:ring-2 focus:ring-[#F2A455]/40 active:scale-[0.98] transition-all duration-200 shadow-lg shadow-orange-500/10">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 00-2 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Edit Employee
                </a>
            </div>

        </div>
    </div>
    <script>
        function toggleDropdown(button) {
            const form = button.closest('.custom-dropdown');
            const menu = form.querySelector('.dropdown-menu');
            const icon = form.querySelector('.dropdown-icon');

            // Check if menu is currently hidden
            const isClosed = menu.classList.contains('opacity-0');

            // Close all other dropdowns on the page first (prevents overlap)
            document.querySelectorAll('.dropdown-menu').forEach(m => closeMenu(m));

            if (isClosed) {
                // Open the dropdown and rotate icon
                menu.classList.remove('opacity-0', 'invisible', 'scale-95');
                menu.classList.add('opacity-100', 'visible', 'scale-100');
                icon.classList.add('rotate-180');
            }
        }

        function closeMenu(menu) {
            menu.classList.remove('opacity-100', 'visible', 'scale-100');
            menu.classList.add('opacity-0', 'invisible', 'scale-95');
            const icon = menu.closest('.custom-dropdown').querySelector('.dropdown-icon');
            if (icon) icon.classList.remove('rotate-180');
        }

        function submitRole(button, roleValue) {
            const form = button.closest('form');
            form.querySelector('input[name="role"]').value = roleValue; // Set hidden input
            form.submit(); // Submit the Laravel form natively
        }

        // Automatically close the dropdown if the user clicks anywhere outside of it
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.custom-dropdown')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => closeMenu(menu));
            }
        });
    </script>
</x-app-layout>