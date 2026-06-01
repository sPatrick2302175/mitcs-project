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

            <div class="mb-4 flex justify-end">
                <a href="{{ route('employees.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    + Add New Employee
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100 border-b-2 border-gray-200 text-gray-600">
                                <th class="p-3 text-sm font-semibold tracking-wide">Emp ID</th>
                                <th class="p-3 text-sm font-semibold tracking-wide">Name</th> <th class="p-3 text-sm font-semibold tracking-wide">Position</th>
                                <th class="p-3 text-sm font-semibold tracking-wide">Department</th>
                                <th class="p-3 text-sm font-semibold tracking-wide">Division</th>
                                <th class="p-3 text-sm font-semibold tracking-wide">Status</th> <th class="p-3 text-sm font-semibold tracking-wide">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($employees as $emp)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="p-3 text-sm text-gray-700">{{ $emp->employee_id_number }}</td>
                                    <td class="p-3 text-sm text-gray-700">{{ $emp->first_name }} {{ $emp->last_name }}</td>
                                    <td class="p-3 text-sm text-gray-700">{{ $emp->position }}</td>
                                    <td class="p-3 text-sm text-gray-700">{{ $emp->department->department_name ?? 'N/A' }}</td>
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
                                                <a href="#" class="text-purple-600 hover:text-purple-900 font-medium hover:underline">Change Role</a>
                                            @elseif(auth()->user()->is_admin === App\Models\User::ROLE_DEPT_ADMIN)
                                                <a href="#" class="text-orange-600 hover:text-orange-900 font-medium hover:underline">Transfer Admin</a>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-6 text-center text-gray-500">No employees found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>