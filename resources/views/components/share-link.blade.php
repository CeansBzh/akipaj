@props(['url', 'label' => 'Lien de partage', 'dark' => false])

@php
    $id = 'share-link-' . md5($url);
    $focusRing = $dark
        ? 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white'
        : 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700';
    $field = $dark
        ? 'bg-white/10 text-white ring-white/25 focus:ring-white'
        : 'bg-white text-gray-900 ring-gray-300 focus:ring-primary-700';
    $copyButton = $dark ? 'text-white hover:bg-white/10' : 'text-primary-800 hover:bg-primary-50';
    $copiedIcon = $dark ? 'text-green-400' : 'text-green-700';
    $tooltip = $dark ? 'bg-white text-gray-950' : 'bg-gray-900 text-white';
    $secondaryButton = $dark
        ? 'text-white ring-1 ring-inset ring-white/30 hover:bg-white/10'
        : 'bg-white text-primary-800 ring-1 ring-inset ring-gray-300 hover:bg-primary-50';
@endphp

<div x-data="{
    url: @js($url),
    status: '',
    copiedFrom: null,
    resetTimer: null,
    async copy(source) {
        clearTimeout(this.resetTimer);
        try {
            await navigator.clipboard.writeText(this.url);
            this.copiedFrom = source;
            this.status = 'Lien copié.';
        } catch {
            this.$refs.field.select();
            this.copiedFrom = null;
            this.status = 'Le lien n’a pas pu être copié : sélectionnez-le pour le copier.';
        }
        this.resetTimer = setTimeout(() => {
            this.copiedFrom = null;
            this.status = '';
        }, 3000);
    },
    canShare: typeof navigator.share === 'function',
    async send() {
        try {
            await navigator.share({ url: this.url });
        } catch {}
    },
}" {{ $attributes }}>
    <label for="{{ $id }}" @class([
        'block text-sm font-semibold sr-only',
        'text-gray-300' => $dark,
        'text-gray-700' => !$dark,
    ])>
        {{ $label }}
    </label>
    <div class="relative">
        <p x-show="copiedFrom === 'field'" x-cloak x-transition.opacity.duration.150ms aria-hidden="true"
            class="{{ $tooltip }} pointer-events-none absolute bottom-full left-1/2 mb-2 -translate-x-1/2 rounded-full px-3 py-1 text-sm font-semibold whitespace-nowrap shadow-lg">
            Lien copié
        </p>
        <input id="{{ $id }}" x-ref="field" type="text" readonly value="{{ $url }}"
            x-on:focus="$el.select()" x-on:click="$el.select(); copy('field')"
            class="{{ $field }} block min-h-11 w-full cursor-pointer truncate rounded-2xl border-0 py-2 ps-4 pe-14 ring-1 ring-inset focus:ring-2">
        <button type="button" x-on:click="copy('button')"
            x-bind:aria-label="copiedFrom === 'button' ? 'Lien copié' : 'Copier le lien'"
            x-bind:title="copiedFrom === 'button' ? 'Lien copié' : 'Copier le lien'" aria-label="Copier le lien"
            title="Copier le lien"
            class="{{ $copyButton }} {{ $focusRing }} absolute inset-y-0 end-0 my-auto me-0.5 inline-flex size-11 items-center justify-center rounded-full transition-colors">
            <svg x-show="copiedFrom !== 'button'" aria-hidden="true" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                class="size-5">
                <rect width="14" height="14" x="8" y="8" rx="2" ry="2" />
                <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2" />
            </svg>
            <svg x-show="copiedFrom === 'button'" x-cloak aria-hidden="true" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"
                class="{{ $copiedIcon }} size-5">
                <path d="M20 6 9 17l-5-5" />
            </svg>
        </button>
    </div>
    <button type="button" x-show="canShare" x-cloak x-on:click="send()"
        class="{{ $secondaryButton }} {{ $focusRing }} mt-3 inline-flex min-h-11 items-center gap-2 rounded-full px-5 py-2 font-semibold transition-colors">
        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
            stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0">
            <path d="M12 2v13" />
            <path d="m16 6-4-4-4 4" />
            <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8" />
        </svg>
        Envoyer…
    </button>
    {{-- Annonce la copie aux lecteurs d'écran ; seul l'échec s'affiche, la bulle et la coche montrant la réussite. --}}
    <p x-text="status" aria-live="polite" x-bind:class="copiedFrom && 'sr-only'" @class([
        'mt-2 empty:hidden',
        'text-gray-200' => $dark,
        'text-gray-800' => !$dark,
    ])></p>
</div>
