<x-guest-layout>
    <!-- Premium Visual Accent Layer & Context -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-orange-50 mb-4 border border-orange-100/50 shadow-inner">
            <svg class="w-6 h-6 text-[#F2A455]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Secure Area</h2>
        <p class="text-sm font-medium text-gray-400 mt-2.5 leading-relaxed px-2">
            {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-6">
        @csrf

        <!-- Password Field -->
        <div class="group">
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2 group-focus-within:text-[#F2A455] transition-colors duration-200">
                {{ __('Password') }} <span class="text-rose-500">*</span>
            </label>
            <x-text-input id="password" 
                class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/10 focus:bg-white transition-all duration-200 sm:text-sm"
                type="password"
                name="password"
                required 
                autocomplete="current-password" 
                placeholder="Enter your current password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs text-rose-500 font-medium" />
        </div>

        <!-- System Action Links Footer Context -->
        <div class="flex items-center justify-end pt-4">
            
            <!-- Primary Confirm Button -->
            <button type="submit" 
                class="inline-flex items-center px-6 py-3 bg-[#F2A455] border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-[#df9344] focus:outline-none focus:ring-2 focus:ring-[#F2A455]/40 active:scale-[0.98] transition-all duration-200 shadow-lg shadow-orange-500/10">
                {{ __('Confirm Password') }}
            </button>
            
        </div>
    </form>
</x-guest-layout>