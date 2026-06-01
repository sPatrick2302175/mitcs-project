<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Divisions') }}
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
                <a href="{{ route('divisions.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 transition ease-in-out duration-150">
                    + Add New Division
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100 border-b-2 border-gray-200 text-gray-600">
                                <th class="p-3 text-sm font-semibold tracking-wide">ID</th>
                                <th class="p-3 text-sm font-semibold tracking-wide">Division Name</th>
                                <th class="p-3 text-sm font-semibold tracking-wide w-32">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($divisions as $div)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="p-3 text-sm text-gray-700">{{ $div->id }}</td>
                                    <td class="p-3 text-sm text-gray-700">{{ $div->division_name }}</td>
                                    <td class="p-3 text-sm space-x-3">
                                        <a href="{{ route('divisions.edit', $div->id) }}" class="text-blue-600 hover:text-blue-900 font-medium hover:underline">Edit</a>
                                        <form action="{{ route('divisions.destroy', $div->id) }}" method="POST" style="display:inline;">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-medium hover:underline" onclick="return confirm('Delete this division?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="p-6 text-center text-gray-500">No divisions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>