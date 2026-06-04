<x-guest-layout>
    <!-- Premium Visual Accent Layer & Context -->
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Reset Password</h2>
        <p class="text-sm font-medium text-gray-400 mt-2.5 leading-relaxed px-2">
            {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6 modern-status" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
        @csrf

        <!-- Email Address Field -->
        <div class="group">
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2 group-focus-within:text-[#F2A455] transition-colors duration-200">
                {{ __('Email Address') }} <span class="text-rose-500">*</span>
            </label>
            <x-text-input id="email" 
                class="block w-full rounded-xl border-gray-200 bg-gray-50/40 px-4 py-3 text-gray-800 placeholder-gray-400 focus:border-[#F2A455] focus:ring focus:ring-[#F2A455]/10 focus:bg-white transition-all duration-200 sm:text-sm" 
                type="email" 
                name="email" 
                :value="old('email')" 
                required 
                autofocus 
                placeholder="Enter your registered email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs text-rose-500 font-medium" />
        </div>

        <!-- System Action Links Footer Context -->
        <div class="flex items-center justify-between pt-4">
            
            <!-- Back to Login Link -->
            <a class="text-xs font-semibold text-gray-400 hover:text-[#F2A455] transition-colors duration-200" href="{{ route('login') }}">
                {{ __('Back to Login') }}
            </a>            
            
            <!-- Primary Reset Button -->
            <button type="submit" 
                class="inline-flex items-center px-6 py-3 bg-[#F2A455] border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-[#df9344] focus:outline-none focus:ring-2 focus:ring-[#F2A455]/40 active:scale-[0.98] transition-all duration-200 shadow-lg shadow-orange-500/10">
                {{ __('Email Reset Link') }}
            </button>
            
        </div>
    </form>
</x-guest-layout>