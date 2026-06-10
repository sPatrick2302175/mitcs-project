<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Corporate Holiday & Suspension Management') }}
        </h2>
    </x-slot>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/airbnb.css">

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Success Notification Card --}}
            @if(session('success'))
                <div class="bg-emerald-50/70 backdrop-blur-sm border border-emerald-100 rounded-xl p-5 shadow-sm transition-all duration-300 animate-fadeIn">
                    <div class="flex items-center">
                        <div class="shrink-0 bg-emerald-100 p-2 rounded-lg">
                            <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <p class="ms-3 text-sm font-bold text-emerald-800">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
            @endif

            {{-- Error Notification Card --}}
            @if($errors->any())
                <div class="bg-rose-50/70 backdrop-blur-sm border border-rose-100 rounded-xl p-5 shadow-sm transition-all duration-300">
                    <div class="flex items-start">
                        <div class="shrink-0 bg-rose-100 p-2 rounded-lg mt-0.5">
                            <svg class="h-5 w-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div class="ms-3">
                            <h3 class="text-sm font-bold text-rose-800">Validation Errors Occurred</h3>
                            <ul class="list-disc pl-5 mt-1 text-xs text-rose-700 font-medium space-y-0.5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                
                {{-- Left Side: Add Holiday Form Component --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:col-span-1">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-gray-800">Add Operational Event</h3>
                        <p class="text-xs text-gray-500">Configure corporate calendar modifications.</p>
                    </div>
                    <hr class="border-gray-100 my-4" />

                    <form action="{{ route('admin.custom-holidays.store') }}" method="POST" class="space-y-4">
                        @csrf
                        
                        <div>
                            <label for="name" class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Holiday/Suspension Name</label>
                            <input type="text" name="name" id="name" required placeholder="e.g., MassKara Festival" 
                                class="w-full bg-gray-50 border border-gray-200 text-sm rounded-xl px-4 py-2.5 focus:bg-white focus:border-indigo-400 focus:ring-0 transition-colors">
                        </div>
                        
                        <div>
                            <label for="type" class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Holiday Classification</label>
                            <select name="type" id="type" class="w-full bg-gray-50 border border-gray-200 text-sm font-semibold text-gray-600 rounded-xl px-4 py-2.5 focus:bg-white focus:border-indigo-400 focus:ring-0 transition-colors">
                                <option value="custom">Announced / Local Holiday (Orange)</option>
                                <option value="regular">Regular / National Holiday (Blue)</option>
                            </select>
                        </div>

                        <div>
                            <label for="dates" class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Target Date Range</label>
                            <div class="relative">
                                <input type="text" name="dates" id="dates" required readonly placeholder="Select calendar entries..." 
                                    class="w-full bg-gray-50 border border-gray-200 text-sm rounded-xl px-4 py-2.5 focus:bg-white focus:border-indigo-400 focus:ring-0 transition-colors cursor-pointer">
                            </div>
                            <p class="text-[11px] text-gray-400 font-medium mt-1">Multi-select is active. Select multiple individual days if needed.</p>
                        </div>

                        <div class="space-y-2 pt-2 border-t border-gray-50">
                            {{-- Checkbox: Is Half Day --}}
                            <label class="flex items-center group cursor-pointer">
                                <input type="checkbox" name="is_half_day" id="is_half_day" value="1" 
                                    class="h-4 w-4 text-indigo-600 focus:ring-0 border-gray-200 rounded-lg cursor-pointer transition-all">
                                <div class="ml-3">
                                    <span class="block text-xs font-bold text-gray-700 group-hover:text-gray-900 transition-colors">Half-Day Operation</span>
                                    <span class="block text-[10px] text-gray-400 font-medium leading-none mt-0.5">Deducts exactly 0.5 from applied ranges.</span>
                                </div>
                            </label>

                            {{-- Checkbox: Is Regular (Annual Recurrence Toggle) --}}
                            <label class="flex items-center group cursor-pointer pt-2">
                                <input type="checkbox" name="is_regular" id="is_regular" value="1" 
                                    class="h-4 w-4 text-indigo-600 focus:ring-0 border-gray-200 rounded-lg cursor-pointer transition-all">
                                <div class="ml-3">
                                    <span class="block text-xs font-bold text-gray-700 group-hover:text-gray-900 transition-colors">Repeats Annually</span>
                                    <span class="block text-[10px] text-gray-400 font-medium leading-none mt-0.5">Auto-applies to future calendar year tracking.</span>
                                </div>
                            </label>
                        </div>

                        <button type="submit" class="w-full flex justify-center py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-200 shadow-md shadow-indigo-500/10 active:scale-[0.98]">
                            Save Calendar Rule
                        </button>
                    </form>
                </div>

                {{-- Right Side: Interactive Table List Component --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden lg:col-span-2">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-xl font-extrabold text-gray-800 tracking-tight">Active Calendar Constraints</h3>
                        <p class="text-sm text-gray-500 font-medium mt-1">These dates bypass balance deduction engines when calculating employee time-off forms.</p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/80 border-b border-gray-100">
                                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Date Matrix</th>
                                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Descriptor Properties</th>
                                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400 text-center">Engine Visibility</th>
                                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50/80">
                                @forelse($holidays as $holiday)
                                    <tr class="hover:bg-gray-50/50 transition-colors duration-200 group">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">
                                            {{ \Carbon\Carbon::parse($holiday->date)->format('M d, Y') }}
                                        </td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-gray-700">{{ $holiday->name }}</div>
                                            <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                                {{-- Type Badge --}}
                                                @if($holiday->type === 'regular')
                                                    <span class="inline-flex px-2 py-0.5 text-[9px] font-extrabold uppercase tracking-wider rounded bg-blue-50 text-blue-600 border border-blue-100">National</span>
                                                @else
                                                    <span class="inline-flex px-2 py-0.5 text-[9px] font-extrabold uppercase tracking-wider rounded bg-orange-50 text-orange-600 border border-orange-100">Local Rule</span>
                                                @endif

                                                {{-- Half Day Badge --}}
                                                @if($holiday->is_half_day)
                                                    <span class="inline-flex px-2 py-0.5 text-[9px] font-extrabold uppercase tracking-wider rounded bg-purple-50 text-purple-600 border border-purple-100">0.5 Day</span>
                                                @endif

                                                {{-- Annual Recurrence Badge --}}
                                                @if($holiday->is_regular)
                                                    <span class="inline-flex px-2 py-0.5 text-[9px] font-extrabold uppercase tracking-wider rounded bg-indigo-50 text-indigo-600 border border-indigo-100">Recurring</span>
                                                @endif
                                            </div>
                                        </td>
                                        
                                        {{-- Interactive Active/Hidden Switch Toggle Status Column --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <form action="{{ route('admin.custom-holidays.toggle', $holiday->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="inline-flex px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all border shadow-sm cursor-pointer active:scale-95
                                                    {{ $holiday->is_active 
                                                        ? 'bg-emerald-50 text-emerald-600 border-emerald-100/60 hover:bg-emerald-100' 
                                                        : 'bg-gray-50 text-gray-400 border-gray-200/60 hover:bg-gray-100' }}">
                                                    {{ $holiday->is_active ? 'Active' : 'Hidden' }}
                                                </button>
                                            </form>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-right space-x-2 text-xs font-bold">
                                            <div class="flex justify-end items-center space-x-3">
                                                <a href="{{ route('admin.custom-holidays.edit', $holiday->id) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors">Edit</a>
                                                
                                                <form action="{{ route('admin.custom-holidays.destroy', $holiday->id) }}" method="POST"  class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-rose-500 hover:text-rose-700 font-bold transition-colors">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-16 text-center">
                                            <div class="w-12 h-12 bg-gray-50 rounded-xl flex items-center justify-center mx-auto mb-3 border border-gray-100">
                                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                            <h3 class="text-sm font-bold text-gray-800 mb-0.5">No Operational Rules Set</h3>
                                            <p class="text-xs text-gray-400 font-medium">Standard leave calculation applies to all upcoming calendar entries.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr("#dates", {
                mode: "multiple",
                dateFormat: "Y-m-d",
                minDate: "today", 
                inline: false,
            });
        });
    </script>
</x-app-layout>