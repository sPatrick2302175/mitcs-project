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
                        
                        @if($employee->user && $employee->user->id !== auth()->id()) 
                            @if(auth()->user()->is_admin === App\Models\User::ROLE_SUPER_ADMIN)
                                <form action="{{ route('employees.changeRole', $employee->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('PUT')
                                    <select name="role" onchange="this.form.submit()" 
                                        class="text-xs font-bold text-purple-700 bg-purple-50 border border-purple-100 rounded-xl cursor-pointer focus:outline-none focus:ring-2 focus:ring-purple-500/20 py-2 pl-3 pr-8 hover:bg-purple-100/70 transition-all duration-200">
                                        <option value="0" {{ $employee->user->is_admin == 0 ? 'selected' : '' }}>Employee</option>
                                        <option value="1" {{ $employee->user->is_admin == App\Models\User::ROLE_DEPT_ADMIN ? 'selected' : '' }}>Dept Admin</option>
                                    </select>
                                </form>
                            @endif
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
                    
                    <ul class="space-y-4">
                        <li class="flex justify-between items-center bg-gray-50/50 p-3 rounded-xl border border-gray-100/40">
                            <span class="text-sm text-gray-600 font-semibold">Vacation Leave</span>
                            <span class="bg-orange-50 text-[#df9344] font-bold text-sm py-1 px-3 rounded-xl border border-orange-100/60">
                                {{ number_format($employee->vacation_leave_balance, 2) }}
                            </span>
                        </li>
                        <li class="flex justify-between items-center bg-gray-50/50 p-3 rounded-xl border border-gray-100/40">
                            <span class="text-sm text-gray-600 font-semibold">Sick Leave</span>
                            <span class="bg-orange-50 text-[#df9344] font-bold text-sm py-1 px-3 rounded-xl border border-orange-100/60">
                                {{ number_format($employee->sick_leave_balance, 2) }}
                            </span>
                        </li>
                        <li class="flex justify-between items-center bg-gray-50/50 p-3 rounded-xl border border-gray-100/40">
                            <span class="text-sm text-gray-600 font-semibold">Mandatory Leave</span>
                            <span class="bg-gray-50 text-gray-700 font-bold text-sm py-1 px-3 rounded-xl border border-gray-200/40">
                                {{ $employee->mandatory_leave_balance }}
                            </span>
                        </li>
                        <li class="flex justify-between items-center bg-gray-50/50 p-3 rounded-xl border border-gray-100/40">
                            <span class="text-sm text-gray-600 font-semibold">Special Privilege</span>
                            <span class="bg-gray-50 text-gray-700 font-bold text-sm py-1 px-3 rounded-xl border border-gray-200/40">
                                {{ $employee->special_privilege_leave_balance }}
                            </span>
                        </li>
                        <li class="flex justify-between items-center bg-gray-50/50 p-3 rounded-xl border border-gray-100/40">
                            <span class="text-sm text-gray-600 font-semibold">Special Emergency</span>
                            <span class="bg-gray-50 text-gray-700 font-bold text-sm py-1 px-3 rounded-xl border border-gray-200/40">
                                {{ $employee->special_emergency_leave_balance }}
                            </span>
                        </li>
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
</x-app-layout>