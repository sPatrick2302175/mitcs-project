<x-guest-layout>
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Create New Password</h2>
        <p class="text-sm font-medium text-gray-400 mt-1">Please enter your email and a strong new password</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-6">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="group">
            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2 group-focus-within:text-[#F2A455] transition-colors duration-200">
                {{ __('Email Address') }} <span class="text-rose-500">*</span>
            </label>
            <input id="email" 
                type="email" 
                name="email" 
                value="{{ old('email', $request->email) }}" 
                required 
                autofocus 
                autocomplete="username" 
                placeholder="Enter your email"
                class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/20 focus:bg-white transition-all duration-200 sm:text-sm outline-none" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs text-rose-500 font-medium" />
        </div>

        <div class="group">
            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2 group-focus-within:text-[#F2A455] transition-colors duration-200">
                {{ __('New Password') }} <span class="text-rose-500">*</span>
            </label>
            <input id="password" 
                type="password"
                name="password"
                required 
                autocomplete="new-password" 
                placeholder="Create a new password"
                class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/20 focus:bg-white transition-all duration-200 sm:text-sm outline-none" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs text-rose-500 font-medium" />
        </div>

        <div class="group">
            <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2 group-focus-within:text-[#F2A455] transition-colors duration-200">
                {{ __('Confirm New Password') }} <span class="text-rose-500">*</span>
            </label>
            <input id="password_confirmation" 
                type="password"
                name="password_confirmation" 
                required 
                autocomplete="new-password" 
                placeholder="Confirm your new password"
                class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/20 focus:bg-white transition-all duration-200 sm:text-sm outline-none" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-xs text-rose-500 font-medium" />
        </div>

        <div class="flex items-center justify-end pt-4">
            
            <button type="submit" 
                class="inline-flex items-center px-6 py-3 bg-[#F2A455] border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-[#df9344] focus:outline-none focus:ring-2 focus:ring-[#F2A455]/40 active:scale-[0.98] transition-all duration-200 shadow-lg shadow-orange-500/10">
                {{ __('Reset Password') }}
            </button>
            
        </div>
    </form>
</x-guest-layout>