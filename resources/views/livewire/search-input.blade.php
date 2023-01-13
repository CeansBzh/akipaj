<div>
    <div class="flex">
        <button type="button"
            class="w-full sm:py-1.5 sm:pl-2 sm:pr-3 sm:flex items-center text-sm leading-6 text-gray-500 sm:text-slate-400 rounded-md sm:ring-1 sm:ring-slate-900/10 sm:shadow-sm sm:hover:ring-slate-400/80"
            x-data="" x-on:click.prevent="$dispatch('open-modal', 'search-input')">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" class="h-7 sm:h-5">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <span class="hidden ml-3 mr-8 sm:block">Rechercher...</span>
        </button>
    </div>

    <x-modal name="search-input" focusable>
        <header class="flex items-center border-b">
            <label id="search-label" for="search-input" class="p-0 xs:py-1.5 xs:px-3">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 text-gray-600 hidden xs:block">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <span class="sr-only">Rechercher</span>
            </label>
            <input type="search" id="search-input" aria-labelledby="search-label" autocomplete="off" autocorrect="off"
                spellcheck="false" enterkeyhint="rechercher" placeholder="Rechercher..." autocapitalize="off"
                maxlength="64" class="grow py-3 border-none focus:ring-0" autofocus wire:model.debounce="searchTerm"
                x-ref="searchInput">
            <button type="reset" aria-label="Annuler" x-on:click="$dispatch('close'),$wire.searchTerm=''"
                class="py-1.5 px-3 text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="sm:hidden">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                <span class="hidden ml-3 mr-5 sm:block">Annuler</span>
            </button>
        </header>
        <div class="px-5 py-6">
            @forelse ($searchResults as $category => $results)
                <div class="mb-5">
                    <p class="font-bold">{{ $category }}</p>
                    <ul class="mt-2">
                        @foreach ($results as $result)
                            <li class="mt-1">
                                <a href="{{ $result['url'] }}"
                                    class="block py-2 px-3 rounded leading-6 text-gray-900 bg-gray-100 group hover:bg-sky-500 hover:text-white">
                                    <div class="flex items-center space-x-5">
                                        <div class="text-gray-500 group-hover:text-white">
                                            {!! $icons[$category] !!}
                                        </div>
                                        <span class="grow">{{ $result['title'] }}</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="feather feather-chevron-right">
                                            <polyline points="9 18 15 12 9 6"></polyline>
                                        </svg>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @empty
                @if (strlen($searchTerm) < 3)
                    <p class="text-gray-500 text-center">Aucun résultat</p>
                @else
                    <p class="text-gray-500 text-center">Aucun résultat pour "<span
                            class="text-black">{{ $searchTerm }}</span>"
                    </p>
                @endif
            @endforelse
        </div>
    </x-modal>
</div>

@push('styles')
    <style>
        input[type="search"]::-webkit-search-decoration,
        input[type="search"]::-webkit-search-cancel-button,
        input[type="search"]::-webkit-search-results-button,
        input[type="search"]::-webkit-search-results-decoration {
            -webkit-appearance: none;
        }
    </style>
@endpush
