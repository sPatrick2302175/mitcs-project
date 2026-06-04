<x-guest-layout>
    <!-- Premium Visual Accent Layer & Context -->
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Create New Password</h2>
        <p class="text-sm font-medium text-gray-400 mt-1">Please enter your email and a strong new password</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-6">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address Field -->
        <div class="group">
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2 group-focus-within:text-[#F2A455] transition-colors duration-200">
                {{ __('Email Address') }} <span class="text-rose-500">*</span>
            </label>
            <x-text-input id="email" 
                class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/10 focus:bg-white transition-all duration-200 sm:text-sm" 
                type="email" 
                name="email" 
                :value="old('email', $request->email)" 
                required 
                autofocus 
                autocomplete="username" 
                placeholder="Enter your email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs text-rose-500 font-medium" />
        </div>

        <!-- New Password Field -->
        <div class="group">
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2 group-focus-within:text-[#F2A455] transition-colors duration-200">
                {{ __('New Password') }} <span class="text-rose-500">*</span>
            </label>
            <x-text-input id="password" 
                class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/10 focus:bg-white transition-all duration-200 sm:text-sm"
                type="password"
                name="password"
                required 
                autocomplete="new-password" 
                placeholder="Create a new password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs text-rose-500 font-medium" />
        </div>

        <!-- Confirm New Password Field -->
        <div class="group">
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2 group-focus-within:text-[#F2A455] transition-colors duration-200">
                {{ __('Confirm New Password') }} <span class="text-rose-500">*</span>
            </label>
            <x-text-input id="password_confirmation" 
                class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/10 focus:bg-white transition-all duration-200 sm:text-sm"
                type="password"
                name="password_confirmation" 
                required 
                autocomplete="new-password" 
                placeholder="Confirm your new password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-xs text-rose-500 font-medium" />
        </div>

        <!-- System Action Links Footer Context -->
        <div class="flex items-center justify-end pt-4">
            
            <!-- Primary Reset Button -->
            <button type="submit" 
                class="inline-flex items-center px-6 py-3 bg-[#F2A455] border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-[#df9344] focus:outline-none focus:ring-2 focus:ring-[#F2A455]/40 active:scale-[0.98] transition-all duration-200 shadow-lg shadow-orange-500/10">
                {{ __('Reset Password') }}
            </button>
            
        </div>
    </form>
</x-guest-layout>