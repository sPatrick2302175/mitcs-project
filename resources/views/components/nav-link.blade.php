@props(['active'])

@php
$classes = ($active ?? false)
            // ACTIVE STATE: Custom hex color for text and bottom indicator. Subtle glowing shadow text.
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-[#F2A455] text-sm font-semibold leading-5 text-[#F2A455] focus:outline-none transition duration-150 ease-in-out'
            
            // INACTIVE STATE: Clean slate text that smoothly transitions to your hex color on hover.
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-[#F2A455] hover:border-[#F2A455]/40 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-300 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>