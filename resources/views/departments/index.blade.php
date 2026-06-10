<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Manage Departments') }}
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

            <div class="mb-8 flex justify-end">
                <a href="{{ route('departments.create') }}" 
                    class="inline-flex items-center px-5 py-3 bg-[#F2A455] border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-[#df9344] focus:outline-none focus:ring-2 focus:ring-[#F2A455]/40 active:scale-[0.98] transition-all duration-200 shadow-lg shadow-orange-500/10">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Add New Department
                </a>
            </div>

            <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 overflow-hidden transition-all duration-300">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/80 border-b border-gray-100">
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Department Name</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400 text-right w-48">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50/80">
                            @forelse($departments as $dept)
                                <tr class="hover:bg-gray-50/50 transition-colors duration-200 group">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">{{ $dept->department_name }}</td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right space-x-4">
                                        <a href="{{ route('departments.edit', $dept->id) }}" 
                                            class="inline-flex items-center text-xs font-bold text-gray-400 hover:text-[#F2A455] uppercase tracking-wider transition-colors duration-200">
                                            Edit
                                        </a>
                                        
                                        <form action="{{ route('departments.destroy', $dept->id) }}" method="POST" class="inline-block">
                                            @csrf 
                                            @method('DELETE')
                                            <button type="submit" 
                                                class="inline-flex items-center text-xs font-bold text-gray-400 hover:text-rose-500 uppercase tracking-wider transition-colors duration-200"
                                                onclick="return confirm('Are you sure you want to delete this department?')">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="p-0 border-none">
                                        <div class="p-12 text-center">
                                            <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-gray-100">
                                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                            </div>
                                            <h3 class="text-lg font-bold text-gray-800 mb-1">No departments found</h3>
                                            <p class="text-sm text-gray-500 font-medium">Get started by creating your first department.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>