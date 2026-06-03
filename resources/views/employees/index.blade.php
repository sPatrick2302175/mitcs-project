<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Employees') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 font-medium text-sm text-green-600 bg-green-100 p-4 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 font-medium text-sm text-red-600 bg-red-100 p-4 rounded-lg">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="mb-6 flex justify-end">
                <a href="{{ route('employees.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    + Add New Employee
                </a>
            </div>

            @forelse($groupedEmployees as $departmentName => $employees)
                @php
                    //Check if this specific department has at least one Dept Admin
                    $hasAdmin = $employees->contains(function ($emp) {
                        return $emp->user && $emp->user->is_admin == App\Models\User::ROLE_DEPT_ADMIN;
                    });
                @endphp

                <div class="mb-10">
                    <div class="flex items-center gap-3 mb-4 pl-2">
                        <h3 class="text-xl font-bold text-gray-800 tracking-tight">
                            {{ $departmentName }}
                        </h3>
                        <span class="bg-indigo-100 text-indigo-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                            {{ $employees->count() }} {{ Str::plural('Employee', $employees->count()) }}
                        </span>
                    </div>

                    @if(!$hasAdmin && $departmentName !== 'Unassigned Department' && auth()->user()->is_admin === App\Models\User::ROLE_SUPER_ADMIN)
                        <div class="mb-3 bg-amber-50 border-l-4 border-amber-400 p-3 flex items-center rounded-r-md shadow-sm">
                            <svg class="h-5 w-5 text-amber-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                            <p class="text-sm text-amber-700 font-medium">Notice: This department currently has no Department Admin assigned. Please assign one below.</p>
                        </div>
                    @endif

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-100 border-b-2 border-gray-200 text-gray-600">
                                        <th class="p-3 text-sm font-semibold tracking-wide">ID No.</th>
                                        <th class="p-3 text-sm font-semibold tracking-wide">Name</th> 
                                        <th class="p-3 text-sm font-semibold tracking-wide">Position</th>
                                        <th class="p-3 text-sm font-semibold tracking-wide">Division</th>
                                        <th class="p-3 text-sm font-semibold tracking-wide">Status</th> 
                                        <th class="p-3 text-sm font-semibold tracking-wide">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($employees as $emp)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="p-3 text-sm text-gray-700">{{ $emp->employee_id_number }}</td>
                                            <td class="p-3 text-sm text-gray-700">{{ $emp->first_name }} {{ $emp->last_name }}</td>
                                            <td class="p-3 text-sm text-gray-700">{{ $emp->position }}</td>
                                            <td class="p-3 text-sm text-gray-700">{{ $emp->division->division_name ?? 'N/A' }}</td>
                                            
                                            <td class="p-3 text-sm">
                                                @if($emp->user)
                                                    <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-bold">Registered</span>
                                                @else
                                                    <span class="bg-gray-100 text-gray-500 text-xs px-2 py-1 rounded-full">Unregistered</span>
                                                @endif
                                            </td>
                                            
                                            <td class="p-3 text-sm space-x-3">
                                                <a href="{{ route('employees.edit', $emp->id) }}" class="text-blue-600 hover:text-blue-900 font-medium hover:underline">Edit</a>
                                                
                                                <form action="{{ route('employees.destroy', $emp->id) }}" method="POST" style="display:inline;">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium hover:underline" onclick="return confirm('Are you sure you want to delete this employee?')">Delete</button>
                                                </form>

                                                @if($emp->user && $emp->user->id !== auth()->id()) 
                                                    <span class="text-gray-300">|</span>
                                                    
                                                    @if(auth()->user()->is_admin === App\Models\User::ROLE_SUPER_ADMIN)
                                                        <form action="{{ route('employees.changeRole', $emp->id) }}" method="POST" class="inline-block">
                                                            @csrf
                                                            @method('PUT')
                                                            <select name="role" onchange="this.form.submit()" class="text-xs font-semibold text-purple-700 bg-purple-50 border border-purple-200 rounded cursor-pointer focus:outline-none focus:ring-1 focus:ring-purple-500 py-1 pl-2 pr-6 hover:bg-purple-100 transition-colors">
                                                                <option value="0" {{ $emp->user->is_admin == 0 ? 'selected' : '' }}>Employee</option>
                                                                <option value="1" {{ $emp->user->is_admin == App\Models\User::ROLE_DEPT_ADMIN ? 'selected' : '' }}>Dept Admin</option>
                                                                        
                                                            </select>
                                                        </form>
                                                    @elseif(auth()->user()->is_admin === App\Models\User::ROLE_DEPT_ADMIN)
                                                       
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center text-gray-500">
                        No employees found.
                    </div>
                </div>
            @endforelse

        </div>
    </div>
</x-app-layout>