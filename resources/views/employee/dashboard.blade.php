<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Employee Portal') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 overflow-hidden transition-all duration-300">
                
                <div class="h-1.5 w-full bg-gradient-to-r from-[#F2A455] to-orange-300"></div>
                
                <div class="p-8 md:p-10">
                    <div class="flex items-center space-x-5">
                        
                        <div class="flex-shrink-0 w-14 h-14 bg-orange-50/80 rounded-2xl flex items-center justify-center border border-orange-100/50 shadow-inner">
                            <svg class="w-7 h-7 text-[#F2A455]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        
                        <div>
                            <h3 class="text-xl font-bold text-gray-800 tracking-tight">
                                Employee Portal
                            </h3>
                            <p class="text-sm font-medium text-gray-500 mt-1">
                                Welcome back. Access your tools, manage your profile, and stay connected.
                            </p>
                        </div>

                    </div>
                </div>
            </div>

            </div>
    </div>
</x-app-layout>