@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
    'class' =>
        'min-h-11 rounded-xl border-gray-300 text-base shadow-xs focus:border-primary-600 focus:ring-primary-600',
]) !!}>
