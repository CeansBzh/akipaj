@props(['url', 'label' => 'Lien de partage', 'dark' => false])

@php
    $id = 'share-link-' . md5($url);
    $focusRing = $dark
        ? 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white'
        : 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700';
    $field = $dark
        ? 'bg-white/10 text-white ring-white/25 focus:ring-white'
        : 'bg-white text-gray-900 ring-gray-300 focus:ring-primary-700';
    $primaryButton = $dark
        ? 'bg-white text-gray-950 hover:bg-gray-200'
        : 'bg-primary-700 text-white hover:bg-primary-800';
    $secondaryButton = $dark
        ? 'text-white ring-1 ring-inset ring-white/30 hover:bg-white/10'
        : 'bg-white text-primary-800 ring-1 ring-inset ring-gray-300 hover:bg-primary-50';
    $button =
        'inline-flex min-h-11 items-center gap-2 rounded-full px-5 py-2 font-semibold transition-colors ' . $focusRing;
@endphp

{{--
    Lien à transmettre : le champ le montre en entier, « Copier le lien » le met dans le presse-papiers et, sur téléphone,
    « Envoyer… » ouvre le partage du système (messages, e-mail…).
--}}
<div x-data="{
    url: @js($url),
    status: '',
    canShare: typeof navigator.share === 'function',
    async copy() {
        try {
            await navigator.clipboard.writeText(this.url);
            this.status = 'Lien copié.';
        } catch {
            this.$refs.field.select();
            this.status = 'Le lien n’a pas pu être copié : sélectionnez-le pour le copier.';
        }
    },
    async send() {
        try {
            await navigator.share({ url: this.url });
        } catch {}
    },
}" {{ $attributes }}>
    <label for="{{ $id }}" @class([
        'block text-sm font-semibold',
        'text-gray-300' => $dark,
        'text-gray-700' => !$dark,
    ])>
        {{ $label }}
    </label>
    <input id="{{ $id }}" x-ref="field" type="text" readonly value="{{ $url }}"
        x-on:focus="$el.select()"
        class="{{ $field }} mt-1 block min-h-11 w-full rounded-2xl border-0 px-4 py-2 ring-1 ring-inset focus:ring-2">
    <div class="mt-3 flex flex-wrap gap-3">
        <button type="button" x-on:click="copy()" class="{{ $button }} {{ $primaryButton }}">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0">
                <rect x="8" y="8" width="12" height="12" rx="2.5" />
                <path d="M16 8V6.5A2.5 2.5 0 0 0 13.5 4h-7A2.5 2.5 0 0 0 4 6.5v7A2.5 2.5 0 0 0 6.5 16H8" />
            </svg>
            Copier le lien
        </button>
        <button type="button" x-show="canShare" x-cloak x-on:click="send()"
            class="{{ $button }} {{ $secondaryButton }}">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0">
                <path d="M12 15V4M7.5 8.5 12 4l4.5 4.5M5 13v5.5A1.5 1.5 0 0 0 6.5 20h11a1.5 1.5 0 0 0 1.5-1.5V13" />
            </svg>
            Envoyer…
        </button>
    </div>
    <p x-text="status" aria-live="polite" @class([
        'mt-2 empty:hidden',
        'text-gray-200' => $dark,
        'text-gray-800' => !$dark,
    ])></p>
    {{ $slot }}
</div>
