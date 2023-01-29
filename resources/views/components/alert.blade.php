<div x-data="{ open: false }" @load.window="open = true" x-init="setTimeout(() => open = false, 6000)">
    <div id="alert-{{ $id }}"
        class="bg-{{ $color }}-100 text-{{ $color }}-700 mb-4 flex rounded-lg p-4" role="alert"
        x-show="open" x-transition:enter="transition duration-200 transform ease-out" x-transition:enter-start="scale-75"
        x-transition:leave="transition duration-100 transform ease-in" x-transition:leave-end="opacity-0 scale-90">
        <svg aria-hidden="true" class="mr-3 h-5 w-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"
            xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd"
                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                clip-rule="evenodd"></path>
        </svg>
        <span class="sr-only">Info</span>

        @if (is_array($message))
            <div>
                <span class="font-medium">Plusieurs messages:</span>
                <ul class="mt-1.5 ml-4 list-inside list-disc text-sm">
                    @foreach ($message as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
        @else
            <div class="ml-3 text-sm font-medium">
                <p>{{ $message }}</p>
            </div>
        @endif

        {{ $slot }}

        <button type="button"
            class="bg-{{ $color }}-100 text-{{ $color }}-500 focus:ring-{{ $color }}-400 hover:bg-{{ $color }}-200 -mx-1.5 -my-1.5 ml-auto inline-flex h-8 w-8 rounded-lg p-1.5 focus:ring-2"
            data-dismiss-target="#alert-{{ $id }}" aria-label="Close" x-on:click="open = false">
            <span class="sr-only">Close</span>
            <svg aria-hidden="true" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd"
                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                    clip-rule="evenodd"></path>
            </svg>
        </button>
    </div>
</div>
