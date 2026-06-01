<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-indigo-100 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-indigo-900 font-bold text-lg">
                    @if(auth()->user()->is_admin === App\Models\User::ROLE_SUPER_ADMIN)
                        Welcome back, Super Administrator.
                        <span class="block text-sm font-normal mt-1 text-indigo-700">You have full system access.</span>
                    @elseif(auth()->user()->is_admin === App\Models\User::ROLE_DEPT_ADMIN)
                        Welcome back, Department Administrator. 
                        <span class="block text-sm font-normal mt-1 text-indigo-700">
                            You are managing: <strong>{{ auth()->user()->department->name ?? 'Your Department' }}</strong>
                        </span>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>