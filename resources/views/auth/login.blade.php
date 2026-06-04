<x-guest-layout>
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Leave Application System</h2>
        <p class="text-sm font-medium text-gray-400 mt-1">National Government Center Bacolod City Official</p>
    </div>

    <x-auth-session-status class="mb-6 modern-status" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Employee ID Number Field -->
        <div class="group">
            
            <input id="employee_id_number" 
                type="text" 
                name="employee_id_number" 
                value="{{ old('employee_id_number') }}" 
                required 
                autofocus 
                autocomplete="off" 
                placeholder="Enter your ID number"
                class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/20 focus:bg-white transition-all duration-200 sm:text-sm outline-none" />
            <x-input-error :messages="$errors->get('employee_id_number')" class="mt-2 text-xs text-rose-500 font-medium" />
        </div>

        <!-- Password Field -->
        <div class="group">
            <div class="flex justify-between items-center mb-2">
                
            </div>
            <input id="password" 
                type="password"
                name="password"
                required 
                autocomplete="current-password" 
                placeholder="Enter your password"
                class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/20 focus:bg-white transition-all duration-200 sm:text-sm outline-none" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs text-rose-500 font-medium" />
        </div>

        <!-- Remember Me -->
        <div class="block">
            <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                <input id="remember_me" type="checkbox" 
                    class="rounded-lg border-gray-200 text-[#F2A455] bg-gray-50/40 shadow-sm focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/20 focus:ring-offset-0 transition-all duration-200 h-4 w-4 outline-none" 
                    name="remember">
                <span class="ms-2.5 text-sm font-medium text-gray-500 group-hover:text-gray-700 transition-colors duration-200">{{ __('Remember me') }}</span>
            </label>
        </div>

        <!-- Action Links & Submit -->
        <div class="flex flex-col space-y-4 pt-2">
            <div class="flex items-center justify-between">
                <div class="flex flex-col space-y-1">
                    <a class="text-xs font-semibold text-gray-400 hover:text-[#F2A455] transition-colors duration-200 self-start" href="{{ route('register') }}">
                        {{ __('Register account') }}
                    </a>
                    
                    @if (Route::has('password.request'))
                        <a class="text-xs font-semibold text-gray-400 hover:text-[#F2A455] transition-colors duration-200 self-start" href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif
                </div>

                <button type="submit" 
                    class="inline-flex items-center px-6 py-3 bg-[#F2A455] border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-[#df9344] focus:outline-none focus:ring-2 focus:ring-[#F2A455]/40 active:scale-[0.98] transition-all duration-200 shadow-lg shadow-orange-500/10">
                    {{ __('Log in') }}
                </button>
            </div>
        </div>
    </form>
</x-guest-layout>