@props([
'name',
'show' => false,
'showDetails' => true,
'photo'
])

<div x-data="{
        show: @js($show),
        showDetails: @js($showDetails),
        focusables() {
            // All focusable element types...
            let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'
            return [...$el.querySelectorAll(selector)]
                // All non-disabled elements...
                .filter(el => ! el.hasAttribute('disabled'))
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() { return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable() },
        prevFocusable() { return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable() },
        nextFocusableIndex() { return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1) },
        prevFocusableIndex() { return Math.max(0, this.focusables().indexOf(document.activeElement)) -1 },
    }" x-init="$watch('show', value => {
        if (value) {
            document.body.classList.add('overflow-y-hidden');
            {{ $attributes->has('focusable') ? 'setTimeout(() => firstFocusable().focus(), 100)' : '' }}
        } else {
            document.body.classList.remove('overflow-y-hidden');
        }
    })" x-on:open-lightbox.window="$event.detail == '{{ $name }}' ? show = true : null" x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false" x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
    x-on:keydown.shift.tab.prevent="prevFocusable().focus()" x-show="show" x-cloak
    class="fixed inset-0 overflow-y-hidden z-50">
    <div x-show="show" class="fixed inset-0 transform transition-all cursor-zoom-out" x-on:click="show = false"
        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-black opacity-95"></div>
    </div>

    <div x-show="show" class="transform transition-all h-full flex flex-col sm:mx-auto"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
        <div class="flex items-center grow select-none cursor-pointer" x-on:click="showDetails = !showDetails">
            {{ $image }}
        </div>
    </div>

    <div class="fixed inset-x-0 top-0 p-4 bg-gradient-to-b from-black/60" x-show="showDetails"
        x-transition:enter="transition ease duration-500 transform" x-transition:enter-start="opacity-0 -translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease duration-300 transform"
        x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-4">
        <div class="flex justify-between text-white sm:mx-5">
            <button class="group" x-on:click="show = false">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="h-7 group-hover:stroke-sky-500">
                    <path d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            @if(isset($photo))
            @can('update', $photo)
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button class="group">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="h-7 group-hover:stroke-sky-500">
                            <circle cx="12" cy="12" r="1"></circle>
                            <circle cx="12" cy="5" r="1"></circle>
                            <circle cx="12" cy="19" r="1"></circle>
                        </svg>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <x-dropdown-link :href="route('photos.edit', $photo)">
                        G??rer la photo
                    </x-dropdown-link>
                </x-slot>
            </x-dropdown>
            @endcan
            @endif
        </div>
    </div>

    <div class="fixed inset-x-0 bottom-0 p-2 bg-gradient-to-t from-black/60" x-show="showDetails"
        x-transition:enter="transition ease duration-500 transform" x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease duration-300 transform"
        x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4">
        {{ $slot }}
    </div>
</div>
