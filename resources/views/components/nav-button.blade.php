@props(['active'])

@php
    $classes = $active ?? false ? 'block py-2 pl-3 pr-4 text-white rounded md:rounded-none bg-sky-500 md:bg-transparent md:text-sky-800 md:p-0 md:border-b-2 md:border-sky-700' : 'block py-2 pl-3 pr-4 text-gray-700 rounded md:rounded-none hover:bg-gray-100 md:hover:bg-transparent md:p-0 md:border-b-2 md:border-transparent md:hover:border-gray-500';
@endphp

<button {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>
