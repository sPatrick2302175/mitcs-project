<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="view-transition" content="same-origin" />

        <title>{{ config('app.name', 'NGC - Leave App') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-cover bg-center bg-no-repeat"
             style="background-image: url('{{ asset('images/ngc.png') }}');">

            <div class="w-full sm:max-w-md mt-6 px-6 py-6 bg-white/95 backdrop-blur-sm shadow-xl overflow-hidden sm:rounded-2xl">
                
                <div class="flex justify-center mb-4 mt-2">
                    <a href="/">
                        <x-application-logo class="w-40 h-auto fill-current text-gray-500" />
                    </a>
                </div>

                {{ $slot }}
            </div>
        </div>
        <script src="https://unpkg.com/@studio-freight/lenis@1.0.36/dist/lenis.min.js"></script>
        <script>
            const lenis = new Lenis({
                duration: 1.2,
                easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
                smoothWheel: true,
                smoothTouch: false
            });

            function raf(time) {
                lenis.raf(time);
                requestAnimationFrame(raf);
            }

            requestAnimationFrame(raf);
        </script>
    </body>
</html>