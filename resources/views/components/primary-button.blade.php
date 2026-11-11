<button
    {{ $attributes->merge(['type' => 'submit', 'class' => 'bg-primary-700 hover:bg-primary-800 active:bg-primary-900 focus-visible:outline-primary-700 inline-flex min-h-11 items-center justify-center gap-2 rounded-full px-5 py-2 text-base font-semibold text-white transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-wait disabled:opacity-60']) }}>
    {{ $slot }}
</button>
