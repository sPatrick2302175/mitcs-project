<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <style>
        /* Modernize Labels */
        .profile-card form label {
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            color: #6b7280 !important;
            margin-bottom: 0.5rem !important;
            transition: color 0.2s ease !important;
        }

        /* Silky Input Fields (Matching bg-gray-50/40, rounded-xl, px-4 py-3) */
        .profile-card form input[type="text"],
        .profile-card form input[type="email"],
        .profile-card form input[type="password"] {
            display: block !important;
            width: 100% !important;
            border-radius: 0.75rem !important; 
            border-color: #e5e7eb !important; 
            background-color: rgba(249, 250, 251, 0.4) !important; 
            padding: 0.75rem 1rem !important; 
            color: #1f2937 !important; 
            transition: all 0.2s ease !important;
        }

        /* Lighting Orange Focus & Ring Glow */
        .profile-card form input[type="text"]:focus,
        .profile-card form input[type="email"]:focus,
        .profile-card form input[type="password"]:focus {
            border-color: #F2A455 !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 4px rgba(242, 164, 85, 0.1) !important; 
            outline: none !important;
        }

        /* Primary Action Buttons (Matching the Update Button) */
        .profile-card form button[type="submit"]:not(.bg-red-600),
        .profile-card form .primary-btn {
            display: inline-flex !important;
            align-items: center !important;
            padding: 0.75rem 1.5rem !important; 
            background-color: #F2A455 !important;
            border: 1px solid transparent !important;
            border-radius: 0.75rem !important; 
            font-weight: 700 !important; 
            font-size: 0.75rem !important; 
            color: #ffffff !important;
            text-transform: uppercase !important;
            letter-spacing: 0.1em !important; 
            box-shadow: 0 10px 15px -3px rgba(242, 164, 85, 0.1), 0 4px 6px -4px rgba(242, 164, 85, 0.1) !important; 
            transition: all 0.2s ease !important;
        }

        .profile-card form button[type="submit"]:not(.bg-red-600):hover,
        .profile-card form .primary-btn:hover {
            background-color: #df9344 !important;
        }

        .profile-card form button[type="submit"]:active {
            transform: scale(0.98) !important;
        }

        /* Adjustments for Delete / Danger Actions to match structure */
        .profile-card form button.bg-red-600,
        .profile-card form button[class*="bg-red"] {
            padding: 0.75rem 1.5rem !important;
            border-radius: 0.75rem !important;
            font-weight: 700 !important;
            font-size: 0.75rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.1em !important;
            transition: transform 0.2s ease !important;
        }
        
        .profile-card form button.bg-red-600:active {
             transform: scale(0.98) !important;
        }
    </style>

    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 p-8 md:p-10 transition-all duration-300 profile-card">
                <div class="max-w-2xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 p-8 md:p-10 transition-all duration-300 profile-card">
                <div class="max-w-2xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100/60 p-8 md:p-10 transition-all duration-300 profile-card">
                <div class="max-w-2xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
            
        </div>
    </div>
</x-app-layout>