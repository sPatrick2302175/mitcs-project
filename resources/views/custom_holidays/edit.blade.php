<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Modify Calendar Rule') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 p-8 md:p-10 transition-all duration-300">
                
                <div class="mb-8 border-b border-gray-100/60 pb-6">
                    <h3 class="text-xl font-bold text-gray-800">Edit Operational Event</h3>
                    <p class="text-sm text-gray-500 mt-1">Updating rule parameter allocations for target entry.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-8 bg-rose-50/70 backdrop-blur-sm border border-rose-100 rounded-xl p-5 transition-all duration-300 animate-fadeIn">
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
                                <ul class="list-disc list-inside text-sm text-rose-600 mt-2 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('admin.custom-holidays.update', $customHoliday->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    
                    <div class="group">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2 group-focus-within:text-[#F2A455] transition-colors duration-200">
                            Target Date
                        </label>
                        <input type="text" disabled value="{{ \Carbon\Carbon::parse($customHoliday->date)->format('F d, Y') }}" 
                            class="block w-full rounded-xl border-gray-200 bg-gray-100/60 px-4 py-3 text-gray-400 transition-all duration-200 sm:text-sm cursor-not-allowed">
                    </div>

                    <div class="group">
                        <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2 group-focus-within:text-[#F2A455] transition-colors duration-200">
                            Holiday/Suspension Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" required value="{{ old('name', $customHoliday->name) }}"
                            class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/10 focus:bg-white transition-all duration-200 sm:text-sm">
                    </div>
                    
                    <div class="group">
                        <label for="type" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2 group-focus-within:text-[#F2A455] transition-colors duration-200">
                            Holiday Classification <span class="text-rose-500">*</span>
                        </label>
                        <select name="type" id="type" required class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/10 focus:bg-white transition-all duration-200 sm:text-sm">
                            <option value="custom" @selected(old('type', $customHoliday->type) === 'custom')>Announced / Local Holiday</option>
                            <option value="regular" @selected(old('type', $customHoliday->type) === 'regular')>Regular / National Holiday</option>
                        </select>
                    </div>

                    <div class="pt-4 mt-2 border-t border-gray-100/60">
                        <h3 class="text-sm font-bold text-gray-700 mb-4 uppercase tracking-wider">Rule Modifiers</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            
                            {{-- Checkbox: Is Half Day --}}
                            <label class="flex items-center p-4 border border-gray-100/60 rounded-xl hover:bg-gray-50/50 cursor-pointer group transition-all duration-200">
                                <input type="checkbox" name="is_half_day" id="is_half_day" value="1" @checked(old('is_half_day', $customHoliday->is_half_day))
                                    class="h-5 w-5 text-[#F2A455] focus:ring-[#F2A455]/40 border-gray-300 rounded cursor-pointer transition-all bg-gray-50/40">
                                <div class="ml-3">
                                    <span class="block text-sm font-bold text-gray-700 group-hover:text-[#F2A455] transition-colors">Half-Day Operation</span>
                                    <span class="block text-xs text-gray-400 mt-0.5">Applies to partial suspensions.</span>
                                </div>
                            </label>

                            {{-- Checkbox: Is Regular (Annual Recurrence) --}}
                            <label class="flex items-center p-4 border border-gray-100/60 rounded-xl hover:bg-gray-50/50 cursor-pointer group transition-all duration-200">
                                <input type="checkbox" name="is_regular" id="is_regular" value="1" @checked(old('is_regular', $customHoliday->is_regular))
                                    class="h-5 w-5 text-[#F2A455] focus:ring-[#F2A455]/40 border-gray-300 rounded cursor-pointer transition-all bg-gray-50/40">
                                <div class="ml-3">
                                    <span class="block text-sm font-bold text-gray-700 group-hover:text-[#F2A455] transition-colors">Repeats Annually</span>
                                    <span class="block text-xs text-gray-400 mt-0.5">Rule applies every year.</span>
                                </div>
                            </label>

                        </div>
                    </div>

                    <div class="flex items-center justify-end border-t border-gray-100/60 pt-6 mt-8 space-x-3">
                        <a href="{{ route('admin.custom-holidays.index') }}" 
                            class="text-sm font-semibold text-gray-500 hover:text-gray-800 px-5 py-3 rounded-xl hover:bg-gray-50 transition-all duration-200">
                            Cancel
                        </a>
                        <button type="submit" 
                            class="inline-flex items-center px-6 py-3 bg-[#F2A455] border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-[#df9344] focus:outline-none focus:ring-2 focus:ring-[#F2A455]/40 active:scale-[0.98] transition-all duration-200 shadow-lg shadow-orange-500/10">
                            Update Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>