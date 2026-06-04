<x-guest-layout>
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Register Account</h2>
        <p class="text-sm font-medium text-gray-400 mt-1">Enter your employee details</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

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

        <div class="group">
            
            <input id="last_name" 
                type="text" 
                name="last_name" 
                value="{{ old('last_name') }}" 
                required 
                autocomplete="family-name" 
                placeholder="Enter your last name"
                class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/20 focus:bg-white transition-all duration-200 sm:text-sm outline-none" />
            <x-input-error :messages="$errors->get('last_name')" class="mt-2 text-xs text-rose-500 font-medium" />
        </div>

        <div class="group">
            
            <input id="email" 
                type="email" 
                name="email" 
                value="{{ old('email') }}" 
                required 
                autocomplete="username" 
                placeholder="Enter your email"
                class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/20 focus:bg-white transition-all duration-200 sm:text-sm outline-none" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs text-rose-500 font-medium" />
        </div>

        <div class="group">
            
            <input id="password" 
                type="password"
                name="password"
                required 
                autocomplete="new-password" 
                placeholder="Create a password"
                class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/20 focus:bg-white transition-all duration-200 sm:text-sm outline-none" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs text-rose-500 font-medium" />
        </div>

        <div class="group">
            
            <input id="password_confirmation" 
                type="password"
                name="password_confirmation" 
                required 
                autocomplete="new-password" 
                placeholder="Confirm your password"
                class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/20 focus:bg-white transition-all duration-200 sm:text-sm outline-none" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-xs text-rose-500 font-medium" />
        </div>

        <div class="flex items-center justify-between pt-4">
            <a class="text-xs font-semibold text-gray-400 hover:text-[#F2A455] transition-colors duration-200" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <button type="submit" 
                class="inline-flex items-center px-6 py-3 bg-[#F2A455] border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-[#df9344] focus:outline-none focus:ring-2 focus:ring-[#F2A455]/40 active:scale-[0.98] transition-all duration-200 shadow-lg shadow-orange-500/10">
                {{ __('Register Account') }}
            </button>
        </div>
    </form>
</x-guest-layout>