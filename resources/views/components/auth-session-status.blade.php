@props(['status'])

@if ($status)
    <p role="status"
        {{ $attributes->merge(['class' => 'rounded-2xl bg-green-50 px-4 py-3 text-green-800 ring-1 ring-green-200']) }}>
        {{ $status }}
    </p>
@endif
