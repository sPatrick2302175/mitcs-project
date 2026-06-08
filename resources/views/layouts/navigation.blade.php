<nav x-data="{ open: false }" class="bg-white/90 backdrop-blur-md sticky top-0 z-50 shadow-sm transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20 transition-all duration-300">
            <div class="flex">
                <div class="shrink-0 flex items-center py-2">
                    <a href="{{ route('dashboard') }}" class="transition-transform duration-200 hover:scale-105">
                        <x-application-logo class="block h-14 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <!-- Dashboard now handles personal leave index and stays active during creation -->
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard', 'leave-requests.create')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <x-nav-link :href="route('leave-requests.history')" :active="request()->routeIs('leave-requests.history')">
                        {{ __('My Leave History') }}
                    </x-nav-link>

                    @if (Auth::user()->is_admin >= App\Models\User::ROLE_DEPT_ADMIN)
                        <x-nav-link :href="route('admin.leave-requests.index')" :active="request()->routeIs('admin.leave-requests.*')">
                            {{ __('Review Leaves') }}
                        </x-nav-link>

                        <x-nav-link :href="route('employees.index')" :active="request()->routeIs('employees.*')">
                            {{ __('Manage Employees') }}
                        </x-nav-link>
                        
                        @if (Auth::user()->is_admin === App\Models\User::ROLE_SUPER_ADMIN)
                            <x-nav-link :href="route('departments.index')" :active="request()->routeIs('departments.*')">
                                {{ __('Manage Departments') }}
                            </x-nav-link>
                        @endif
                        
                        <x-nav-link :href="route('divisions.index')" :active="request()->routeIs('divisions.*')">
                            {{ __('Manage Divisions') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-4 py-2 border border-gray-100/80 text-sm leading-4 font-medium rounded-full text-gray-600 bg-gray-50/50 hover:bg-gray-100/80 hover:text-gray-900 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-2 text-gray-400">
                                <svg class="fill-current h-4 w-4 transition-transform duration-200" :class="{'rotate-180': open}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-50 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation Links -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white/95 backdrop-blur-md border-b border-gray-100">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard', 'leave-requests.create')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('leave-requests.history')" :active="request()->routeIs('leave-requests.history')">
                {{ __('My Leave History') }}
            </x-responsive-nav-link>

            @if (Auth::user()->is_admin >= App\Models\User::ROLE_DEPT_ADMIN)
                <x-responsive-nav-link :href="route('admin.leave-requests.index')" :active="request()->routeIs('admin.leave-requests.*')">
                    {{ __('Review Leaves') }}
                </x-responsive-nav-link>
                
                <x-responsive-nav-link :href="route('employees.index')" :active="request()->routeIs('employees.*')">
                    {{ __('Manage Employees') }}
                </x-responsive-nav-link>
                
                @if (Auth::user()->is_admin === App\Models\User::ROLE_SUPER_ADMIN)
                    <x-responsive-nav-link :href="route('departments.index')" :active="request()->routeIs('departments.*')">
                        {{ __('Manage Departments') }}
                    </x-responsive-nav-link>
                @endif
                
                <x-responsive-nav-link :href="route('divisions.index')" :active="request()->routeIs('divisions.*')">
                    {{ __('Manage Divisions') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <div class="pt-4 pb-1 border-t border-gray-100">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>