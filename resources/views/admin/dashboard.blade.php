<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 overflow-hidden transition-all duration-300">
                
                <div class="h-1.5 w-full bg-gradient-to-r from-[#F2A455] to-orange-300"></div>
                
                <div class="p-8 md:p-10">
                    <div class="flex items-start space-x-5">
                        
                        <div class="flex-shrink-0 w-14 h-14 bg-orange-50/80 rounded-2xl flex items-center justify-center border border-orange-100/50 shadow-inner">
                            <svg class="w-7 h-7 text-[#F2A455]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        
                        <div class="pt-1">
                            @if(auth()->user()->is_admin === App\Models\User::ROLE_SUPER_ADMIN)
                                <h3 class="text-xl font-bold text-gray-800 tracking-tight">
                                    Welcome back, Super Administrator.
                                </h3>
                                <p class="text-sm font-medium text-gray-500 mt-1.5">
                                    You have full system access.
                                </p>
                            @elseif(auth()->user()->is_admin === App\Models\User::ROLE_DEPT_ADMIN)
                                <h3 class="text-xl font-bold text-gray-800 tracking-tight">
                                    Welcome back, Department Administrator.
                                </h3>
                                <p class="text-sm font-medium text-gray-500 mt-1.5">
                                    You are managing: <span class="font-semibold text-gray-700 bg-gray-100 px-2.5 py-0.5 rounded-lg border border-gray-200/60 ml-1">{{ auth()->user()->department->name ?? 'Your Department' }}</span>
                                </p>
                            @endif
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>