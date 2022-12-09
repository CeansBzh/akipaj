@props(['disabled' => false])

<select {{ $disabled ? 'disabled' : '' }} {!! $attributes !!} multiple>
    {{ $slot }}
</select>

@once
@push('scripts')
<script src="{{ Vite::asset('resources/js/multi-select-dropdown.js') }}"></script>
@endpush
@endonce