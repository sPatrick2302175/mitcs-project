<x-guest-layout>
    

    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

        <div class="group">
            <x-text-input id="employee_id_number" 
                class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/10 focus:bg-white transition-all duration-200 sm:text-sm" 
                type="text" 
                name="employee_id_number" 
                :value="old('employee_id_number')" 
                required 
                autofocus 
                autocomplete="off" 
                placeholder="Enter your ID number" />
            <x-input-error :messages="$errors->get('employee_id_number')" class="mt-2 text-xs text-rose-500 font-medium" />
        </div>

        <div class="group">
           
            <x-text-input id="last_name" 
                class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/10 focus:bg-white transition-all duration-200 sm:text-sm" 
                type="text" 
                name="last_name" 
                :value="old('last_name')" 
                required 
                autocomplete="family-name" 
                placeholder="Enter your last name" />
            <x-input-error :messages="$errors->get('last_name')" class="mt-2 text-xs text-rose-500 font-medium" />
        </div>

        <div class="group">
            
            <x-text-input id="email" 
                class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/10 focus:bg-white transition-all duration-200 sm:text-sm" 
                type="email" 
                name="email" 
                :value="old('email')" 
                required 
                autocomplete="username" 
                placeholder="Enter your email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs text-rose-500 font-medium" />
        </div>

        <div class="group">
            
            <x-text-input id="password" 
                class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/10 focus:bg-white transition-all duration-200 sm:text-sm"
                type="password"
                name="password"
                required 
                autocomplete="new-password" 
                placeholder="Create a password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs text-rose-500 font-medium" />
        </div>

        <div class="group">
            
            <x-text-input id="password_confirmation" 
                class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/10 focus:bg-white transition-all duration-200 sm:text-sm"
                type="password"
                name="password_confirmation" 
                required 
                autocomplete="new-password" 
                placeholder="Confirm your password" />
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