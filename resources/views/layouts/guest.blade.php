<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'NGC - Leave App') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <!-- 1. CHANGED: Removed bg-gray-100 and added background image configuration -->
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-cover bg-center bg-no-repeat"
             style="background-image: url('{{ asset('images/ngc.png') }}');">

            <!-- 2. CHANGED: Added backdrop-blur-sm and slightly translucent white (bg-white/95) so the background image looks premium behind it -->
            <div class="w-full sm:max-w-md mt-6 px-6 py-6 bg-white/95 backdrop-blur-sm shadow-xl overflow-hidden sm:rounded-2xl">
                
                <!-- 3. CHANGED: Moved the logo link inside the container and added "flex justify-center" to center it -->
                <div class="flex justify-center mb-4 mt-2">
                    <a href="/">
                        <x-application-logo class="w-40 h-auto fill-current text-gray-500" />
                    </a>
                </div>

                {{ $slot }}
            </div>
        </div>
    </body>
</html>