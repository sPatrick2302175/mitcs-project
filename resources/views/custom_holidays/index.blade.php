<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Corporate Holiday & Suspension Management') }}
        </h2>
    </x-slot>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        .flatpickr-calendar {
            font-family: inherit;
            border-radius: 1rem !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.03), 0 8px 10px -6px rgba(0, 0, 0, 0.03) !important;
            border: 1px solid #f3f4f6 !important;
            padding: 0.25rem;
        }
        
        .flatpickr-day.selected, 
        .flatpickr-day.selected:hover {
            background: #F2A455 !important;
            border-color: #F2A455 !important;
            border-radius: 0.5rem !important;
        }
    </style>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Success Notification Card --}}
            @if(session('success'))
                <div class="bg-emerald-50/70 backdrop-blur-sm border border-emerald-100 rounded-xl p-5 transition-all duration-300 animate-fadeIn shadow-sm">
                    <div class="flex items-center">
                        <div class="shrink-0">
                            <svg class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <p class="ms-3 text-sm font-semibold text-emerald-800">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
            @endif

            {{-- Error/Validation Notification Card --}}
            @if($errors->any())
                <div class="bg-rose-50/70 backdrop-blur-sm border border-rose-100 rounded-xl p-5 transition-all duration-300 animate-fadeIn shadow-sm">
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
                            <ul class="list-disc list-inside text-xs text-rose-600 mt-1.5 space-y-1 font-medium">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                
                {{-- Left Side: Add Holiday Form Component --}}
                <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 p-6 lg:p-8 lg:col-span-1 transition-all duration-300">
                    <div class="mb-5">
                        <h3 class="text-base font-bold text-gray-800 uppercase tracking-wider">Add Operational Event</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Configure corporate calendar modifications.</p>
                    </div>
                    <hr class="border-gray-100/80 my-4" />

                    <form action="{{ route('admin.custom-holidays.store') }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <div class="group">
                            <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2 group-focus-within:text-[#F2A455] transition-colors duration-200">
                                Holiday/Suspension Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" required placeholder="e.g., MassKara Festival" 
                                class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/10 focus:bg-white transition-all duration-200 sm:text-sm">
                        </div>
                        
                        <div class="group">
                            <label for="type" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2 group-focus-within:text-[#F2A455] transition-colors duration-200">
                                Holiday Classification <span class="text-rose-500">*</span>
                            </label>
                            <select name="type" id="type" 
                                class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/10 focus:bg-white transition-all duration-200 sm:text-sm">
                                <option value="custom">Announced / Local Holiday (Orange)</option>
                                <option value="regular">Regular / National Holiday (Blue)</option>
                            </select>
                        </div>

                        <div class="group">
                            <label for="dates" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2 group-focus-within:text-[#F2A455] transition-colors duration-200">
                                Target Date Range <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="dates" id="dates" required readonly placeholder="Select calendar entries..." 
                                class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/10 focus:bg-white transition-all duration-200 sm:text-sm cursor-pointer">
                            <p class="text-[11px] text-gray-400 font-medium mt-1.5 leading-relaxed">Multi-select is active. Select multiple individual days if needed.</p>
                        </div>

                        <div class="space-y-3 pt-3 border-t border-gray-100/60">
                            {{-- Checkbox: Is Half Day --}}
                            <label class="flex items-start group cursor-pointer">
                                <input type="checkbox" name="is_half_day" id="is_half_day" value="1" 
                                    class="h-4 w-4 rounded-md border-gray-200 text-[#F2A455] focus:ring focus:ring-[#F2A455]/10 cursor-pointer transition-all mt-0.5">
                                <div class="ml-3">
                                    <span class="block text-xs font-semibold text-gray-700 group-hover:text-gray-900 transition-colors">Half-Day Operation</span>
                                    <span class="block text-[10px] text-gray-400 font-medium leading-normal mt-0.5">Deducts exactly 0.5 from applied ranges.</span>
                                </div>
                            </label>

                            {{-- Checkbox: Is Regular (Annual Recurrence Toggle) --}}
                            <label class="flex items-start group cursor-pointer pt-1">
                                <input type="checkbox" name="is_regular" id="is_regular" value="1" 
                                    class="h-4 w-4 rounded-md border-gray-200 text-[#F2A455] focus:ring focus:ring-[#F2A455]/10 cursor-pointer transition-all mt-0.5">
                                <div class="ml-3">
                                    <span class="block text-xs font-semibold text-gray-700 group-hover:text-gray-900 transition-colors">Repeats Annually</span>
                                    <span class="block text-[10px] text-gray-400 font-medium leading-normal mt-0.5">Auto-applies to future calendar year tracking.</span>
                                </div>
                            </label>
                        </div>

                        <button type="submit" 
                            class="w-full inline-flex items-center justify-center px-6 py-3 bg-[#F2A455] border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-[#df9344] focus:outline-none focus:ring-2 focus:ring-[#F2A455]/40 active:scale-[0.98] transition-all duration-200 shadow-lg shadow-orange-500/10">
                            Save Calendar Rule
                        </button>
                    </form>
                </div>

                {{-- Right Side: Interactive Table List Component --}}
                <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 overflow-hidden lg:col-span-2 transition-all duration-300">
                    <div class="p-6 lg:p-8 border-b border-gray-100/60">
                        <h3 class="text-base font-bold text-gray-800 uppercase tracking-wider">Active Calendar Constraints</h3>
                        <p class="text-xs text-gray-400 mt-0.5">These dates bypass balance deduction engines when calculating employee time-off forms.</p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50 border-b border-gray-100/60">
                                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-400">Date Matrix</th>
                                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-400">Descriptor Properties</th>
                                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-400 text-center">Engine Visibility</th>
                                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-400 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50/60">
                                @forelse($holidays as $holiday)
                                    <tr class="hover:bg-gray-50/30 transition-colors duration-200 group">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-800">
                                            {{ \Carbon\Carbon::parse($holiday->date)->format('M d, Y') }}
                                        </td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-gray-700">{{ $holiday->name }}</div>
                                            <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                                {{-- Type Badge --}}
                                                @if($holiday->type === 'regular')
                                                    <span class="inline-flex px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider rounded bg-blue-50 text-blue-600 border border-blue-100/60">National</span>
                                                @else
                                                    <span class="inline-flex px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider rounded bg-orange-50 text-orange-600 border border-orange-100/60">Local Rule</span>
                                                @endif

                                                {{-- Half Day Badge --}}
                                                @if($holiday->is_half_day)
                                                    <span class="inline-flex px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider rounded bg-purple-50 text-purple-600 border border-purple-100/60">0.5 Day</span>
                                                @endif

                                                {{-- Annual Recurrence Badge --}}
                                                @if($holiday->is_regular)
                                                    <span class="inline-flex px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider rounded bg-amber-50 text-amber-600 border border-amber-100/60">Recurring</span>
                                                @endif
                                            </div>
                                        </td>
                                        
                                        {{-- Interactive Status Column --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <form action="{{ route('admin.custom-holidays.toggle', $holiday->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="inline-flex px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all border shadow-sm cursor-pointer active:scale-95
                                                    {{ $holiday->is_active 
                                                        ? 'bg-emerald-50 text-emerald-600 border-emerald-100/60 hover:bg-emerald-100 hover:border-emerald-200' 
                                                        : 'bg-gray-50 text-gray-400 border-gray-200/60 hover:bg-gray-100 hover:border-gray-300' }}">
                                                    {{ $holiday->is_active ? 'Active' : 'Hidden' }}
                                                </button>
                                            </form>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-semibold">
                                            <div class="flex justify-end items-center space-x-3.5">
                                                <a href="{{ route('admin.custom-holidays.edit', $holiday->id) }}" class="text-[#F2A455] hover:text-[#df9344] transition-colors">Edit</a>
                                                
                                                <form action="{{ route('admin.custom-holidays.destroy', $holiday->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-rose-500 hover:text-rose-700 transition-colors">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-16 text-center">
                                            <div class="w-12 h-12 bg-gray-50 rounded-xl flex items-center justify-center mx-auto mb-3 border border-gray-100/80">
                                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                            <h3 class="text-sm font-bold text-gray-700">No Operational Rules Set</h3>
                                            <p class="text-xs text-gray-400 font-medium mt-0.5">Standard leave calculation applies to all upcoming calendar entries.</p>
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