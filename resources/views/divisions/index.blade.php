<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Manage Divisions') }}
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
                <a href="{{ route('divisions.create') }}" 
                    class="inline-flex items-center px-5 py-3 bg-[#F2A455] border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-[#df9344] focus:outline-none focus:ring-2 focus:ring-[#F2A455]/40 active:scale-[0.98] transition-all duration-200 shadow-lg shadow-orange-500/10">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Add New Division
                </a>
            </div>

            @forelse($departments as $dept)
                <div class="mb-12 relative">
                    <div class="flex items-center gap-4 mb-5 px-2">
                        <h3 class="text-xl font-extrabold text-gray-800 tracking-tight">
                            {{ $dept->department_name }}
                        </h3>
                        <span class="bg-orange-50 text-[#df9344] border border-orange-100/60 text-xs font-bold px-3 py-1 rounded-xl shadow-sm">
                            {{ $dept->divisions->count() }} {{ Str::plural('Division', $dept->divisions->count()) }}
                        </span>
                    </div>

                    <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 overflow-hidden transition-all duration-300">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50/80 border-b border-gray-100">
                                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Division Name</th>
                                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Code</th>
                                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50/80">
                                    @forelse($dept->divisions as $div)
                                        <tr class="hover:bg-gray-50/50 transition-colors duration-200 group">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800 group-hover:text-gray-900 transition-colors">
                                                {{ $div->division_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-600">
                                                @if($div->code)
                                                    <span class="bg-gray-100 text-gray-600 px-2.5 py-1 rounded-md text-xs font-semibold">{{ $div->code }}</span>
                                                @else
                                                    <span class="text-gray-400 italic text-xs">N/A</span>
                                                @endif
                                            </td>
                                            
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right space-x-4">
                                                <a href="{{ route('divisions.edit', $div->id) }}" 
                                                    class="inline-flex items-center text-xs font-bold text-gray-400 hover:text-[#F2A455] uppercase tracking-wider transition-colors duration-200">
                                                    Edit
                                                </a>
                                                <form action="{{ route('divisions.destroy', $div->id) }}" method="POST" class="inline-block">
                                                    @csrf 
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                        class="inline-flex items-center text-xs font-bold text-gray-400 hover:text-rose-500 uppercase tracking-wider transition-colors duration-200" 
                                                        onclick="return confirm('Are you sure you want to delete this division?')">
                                                        Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-6 py-8 text-center">
                                                <span class="text-sm font-medium text-gray-400 italic">No divisions setup under this department yet.</span>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 p-12 text-center">
                    <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-gray-100">
                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-1">No departments found</h3>
                    <p class="text-sm text-gray-500 font-medium">Please add departments first before adding divisions.</p>
                </div>
            @endforelse

        </div>
    </div>
</x-app-layout>