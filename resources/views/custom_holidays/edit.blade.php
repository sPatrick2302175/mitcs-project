<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Modify Calendar Rule') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Edit Operational Event</h3>
                    <p class="text-xs text-gray-500">Updating rule parameter allocations for target entry.</p>
                </div>
                <hr class="border-gray-100 my-4" />

                <form action="{{ route('admin.custom-holidays.update', $customHoliday->id) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Target Date</label>
                        <input type="text" disabled value="{{ \Carbon\Carbon::parse($customHoliday->date)->format('F d, Y') }}" 
                            class="w-full bg-gray-100 border border-gray-200 text-sm font-semibold text-gray-500 rounded-xl px-4 py-2.5 cursor-not-allowed">
                    </div>

                    <div>
                        <label for="name" class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Holiday/Suspension Name</label>
                        <input type="text" name="name" id="name" required value="{{ old('name', $customHoliday->name) }}"
                            class="w-full bg-gray-50 border border-gray-200 text-sm rounded-xl px-4 py-2.5 focus:bg-white focus:border-indigo-400 focus:ring-0 transition-colors">
                    </div>
                    
                    <div>
                        <label for="type" class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Holiday Classification</label>
                        <select name="type" id="type" class="w-full bg-gray-50 border border-gray-200 text-sm font-semibold text-gray-600 rounded-xl px-4 py-2.5 focus:bg-white focus:border-indigo-400 focus:ring-0 transition-colors">
                            <option value="custom" {{ old('type', $customHoliday->type) === 'custom' ? 'selected' : '' }}>Announced / Local Holiday</option>
                            <option value="regular" {{ old('type', $customHoliday->type) === 'regular' ? 'selected' : '' }}>Regular / National Holiday</option>
                        </select>
                    </div>

                    <div class="space-y-2 pt-2 border-t border-gray-50">
                        {{-- Checkbox: Is Half Day --}}
                        <label class="flex items-center group cursor-pointer">
                            <input type="checkbox" name="is_half_day" id="is_half_day" value="1" {{ old('is_half_day', $customHoliday->is_half_day) ? 'checked' : '' }}
                                class="h-4 w-4 text-indigo-600 focus:ring-0 border-gray-200 rounded-lg cursor-pointer transition-all">
                            <div class="ml-3">
                                <span class="block text-xs font-bold text-gray-700 group-hover:text-gray-900 transition-colors">Half-Day Operation</span>
                            </div>
                        </label>

                        {{-- Checkbox: Is Regular (Annual Recurrence) --}}
                        <label class="flex items-center group cursor-pointer pt-2">
                            <input type="checkbox" name="is_regular" id="is_regular" value="1" {{ old('is_regular', $customHoliday->is_regular) ? 'checked' : '' }}
                                class="h-4 w-4 text-indigo-600 focus:ring-0 border-gray-200 rounded-lg cursor-pointer transition-all">
                            <div class="ml-3">
                                <span class="block text-xs font-bold text-gray-700 group-hover:text-gray-900 transition-colors">Repeats Annually</span>
                            </div>
                        </label>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <a href="{{ route('admin.custom-holidays.index') }}" class="w-1/3 text-center py-2.5 border border-gray-200 text-gray-600 text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-gray-50 transition-all">
                            Cancel
                        </a>
                        <button type="submit" class="w-2/3 flex justify-center py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-200 shadow-md shadow-indigo-500/10 active:scale-[0.98]">
                            Update Changes
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>