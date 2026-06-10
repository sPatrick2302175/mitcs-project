<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Manage Employees') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-8 bg-emerald-50/70 backdrop-blur-sm border border-emerald-100 rounded-xl p-5 shadow-sm transition-all duration-300 animate-fadeIn">
                    <div class="flex items-center">
                        <div class="shrink-0 bg-emerald-100 p-2 rounded-lg">
                            <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <p class="ms-3 text-sm font-bold text-emerald-800">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-8 bg-rose-50/70 backdrop-blur-sm border border-rose-100 rounded-xl p-5 shadow-sm transition-all duration-300 animate-fadeIn">
                    <div class="flex items-start">
                        <div class="shrink-0 bg-rose-100 p-2 rounded-lg">
                            <svg class="h-5 w-5 text-rose-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" /></svg>
                        </div>
                        <div class="ms-3 mt-1">
                            <ul class="list-none text-sm font-semibold text-rose-800 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <div class="mb-8 flex justify-end">
                <a href="{{ route('employees.create') }}" 
                    class="inline-flex items-center px-5 py-3 bg-[#F2A455] border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-[#df9344] focus:outline-none focus:ring-2 focus:ring-[#F2A455]/40 active:scale-[0.98] transition-all duration-200 shadow-lg shadow-orange-500/10">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Add New Employee
                </a>
            </div>

            @forelse($groupedEmployees as $departmentName => $employees)
                @php
                    // Check if this specific department has at least one Dept Admin
                    $hasAdmin = $employees->contains(function ($emp) {
                        return $emp->user && in_array($emp->user->is_admin, [
                            App\Models\User::ROLE_DEPT_HEAD, 
                            App\Models\User::ROLE_ADMIN_OFFICER
                        ]);
                    });
                @endphp

                <div class="mb-12 relative">
                    <div class="flex items-center gap-4 mb-5 px-2">
                        <h3 class="text-xl font-extrabold text-gray-800 tracking-tight">
                            {{ $departmentName }}
                        </h3>
                        <span class="bg-orange-50 text-[#df9344] border border-orange-100/60 text-xs font-bold px-3 py-1 rounded-xl shadow-sm">
                            {{ $employees->count() }} {{ Str::plural('Employee', $employees->count()) }}
                        </span>
                    </div>

                    @if(!$hasAdmin && $departmentName !== 'Unassigned Department' && auth()->user()->is_admin === App\Models\User::ROLE_SUPER_ADMIN)
                        <div class="mb-5 bg-amber-50/80 backdrop-blur-sm border border-amber-200/60 p-4 flex items-center rounded-xl shadow-sm">
                            <div class="shrink-0 bg-amber-100/50 p-2 rounded-lg mr-3">
                                <svg class="h-5 w-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                            </div>
                            <p class="text-sm text-amber-800 font-semibold tracking-wide">
                                Notice: This department currently has no Department Admin assigned. Please assign one via a profile.
                            </p>
                        </div>
                    @endif

                    <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 overflow-hidden transition-all duration-300">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50/80 border-b border-gray-100">
                                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">ID No.</th>
                                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Name</th> 
                                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Position</th>
                                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Division</th>
                                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Status</th> 
                                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50/80">
                                    @foreach($employees as $emp)
                                        <tr class="hover:bg-gray-50/50 transition-colors duration-200 group">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-600 group-hover:text-gray-900 transition-colors">{{ $emp->employee_id_number }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">{{ $emp->first_name }} {{ $emp->last_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-600">{{ $emp->position_code }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-600">
                                                @if($emp->division)
                                                    <span class="bg-gray-100 text-gray-600 px-2.5 py-1 rounded-md text-xs font-semibold">{{ $emp->division->division_name }}</span>
                                                @else
                                                    <span class="text-gray-400 italic text-xs">N/A</span>
                                                @endif
                                            </td>
                                            
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if($emp->user)
                                                    <span class="bg-emerald-50 text-emerald-700 border border-emerald-100/80 text-xs px-3 py-1.5 rounded-xl font-bold inline-flex items-center shadow-sm">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 me-2 animate-pulse"></span>
                                                        Registered
                                                    </span>
                                                @else
                                                    <span class="bg-gray-50 text-gray-500 border border-gray-200/60 text-xs px-3 py-1.5 rounded-xl font-medium inline-flex items-center">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 me-2"></span>
                                                        Unregistered
                                                    </span>
                                                @endif
                                            </td>
                                            
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                                <a href="{{ route('employees.show', $emp->id) }}" 
                                                    class="inline-flex items-center text-xs font-bold text-gray-400 hover:text-[#F2A455] uppercase tracking-wider transition-colors duration-200">
                                                    View Profile
                                                    <svg class="w-4 h-4 ml-1 opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 p-12 text-center">
                    <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-gray-100">
                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-1">No employees found</h3>
                    <p class="text-sm text-gray-500 font-medium">Get started by adding your first team member.</p>
                </div>
            @endforelse

        </div>
    </div>
</x-app-layout>