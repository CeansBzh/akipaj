@props(['active'])

@php
$classes = ($active ?? false)
            ? 'flex items-center bg-sky-800 text-white py-2 pl-4 hover:bg-sky-600'
            : 'flex items-center text-white opacity-75 hover:opacity-100 py-2 pl-4 hover:bg-sky-600';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>