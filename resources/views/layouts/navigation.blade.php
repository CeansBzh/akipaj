<nav x-data="{ open: false }" class="bg-white border-gray-200">
    <div class="container flex flex-wrap items-center justify-between mx-auto px-2 sm:px-4 py-2.5">
        {{-- Logo --}}
        <a href="{{ url('/') }}" class="flex items-center">
            <svg viewBox="0 0 48 58" fill="none" xmlns="http://www.w3.org/2000/svg" class="h-12">
                <path
                    d="M36.3885 6.14278L6.06381 55.2067C8.03098 55.3719 10.6753 55.4709 13.6326 55.4058C23.1945 43.0471 30.2202 28.9267 34.2935 13.8812L36.3885 6.14278Z"
                    fill="#0ea5e9" fill-opacity="0.5" />
                <path
                    d="M38.5289 0.354818C44.427 7.43984 46.3765 41.5354 37.0222 48.995C31.5835 53.332 24.0237 55.319 16.158 55.4921C28.364 39.3951 36.0946 20.3191 38.5289 0.354818Z"
                    fill="#0ea5e9" />
            </svg>
        </a>
        <div class="flex items-center md:order-2">
            {{-- User dropdown --}}
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button type="button" id="user-menu-button"
                        class="flex mr-3 text-sm bg-gray-800 rounded-full md:mr-0 focus:ring-4 focus:ring-sky-200">
                        <span class="sr-only">Ouvrir le menu utilisateur</span>
                        <img class="w-8 h-8 rounded-full"
                            src="{{ Auth::user()->profile_picture_path ?? Vite::asset('resources/images/default-pfp.png') }}"
                            alt="Photo de profil">
                    </button>
                </x-slot>
                <x-slot name="content">
                    <div class="px-4 py-3">
                        <span class="block text-sm text-gray-900">{{ Auth::user()->name }}</span>
                        <span class="block text-sm font-medium text-gray-500 truncate">{{ Auth::user()->email }}</span>
                    </div>
                    <ul class="py-1" aria-labelledby="user-menu-button">
                        <li>
                            <x-dropdown-link :href="route('profile.index')">
                                {{ __('Mon profil') }}
                            </x-dropdown-link>
                        </li>
                        @if(Auth::user()->hasRole('admin'))
                        <li>
                            <x-dropdown-link :href="route('admin.index')">
                                {{ __('Panneau admin') }}
                            </x-dropdown-link>
                        </li>
                        @endif
                        {{-- Authentication --}}
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault();
                                            this.closest('form').submit();">
                                    {{ __('Déconnexion') }}
                                </x-dropdown-link>
                            </form>
                        </li>
                    </ul>
                </x-slot>
            </x-dropdown>
            {{-- Main menu burger button --}}
            <button type="button" @click="open = !open"
                class="inline-flex items-center p-2 ml-1 text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200"
                aria-controls="mobile-menu" aria-expanded="false">
                <span class="sr-only">Open main menu</span>
                <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" viewBox="0 0 22 22" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                    class="w-6 h-6">
                    <line x1="3" y1="12" x2="19" y2="12"></line>
                    <line x1="3" y1="6" x2="19" y2="6"></line>
                    <line x1="3" y1="18" x2="19" y2="18"></line>
                </svg>
            </button>
        </div>
        {{-- Main menu links --}}
        <div :class="{'block': open, 'hidden': !open}"
            class="items-center justify-between hidden w-full md:flex md:w-auto md:order-1" id="mobile-menu">
            <ul
                class="flex flex-col p-4 mt-4 rounded-lg bg-gray-50 md:flex-row md:space-x-12 md:mt-0 md:text-[0.95rem] md:font-medium md:border-0 md:bg-white">
                <li>
                    <x-nav-link :href="url('/')" :active="(url()->current() == url('/'))">
                        <span>Accueil</span>
                    </x-nav-link>
                </li>
                <li>
                    <x-nav-link :href="route('articles.index')" :active="request()->routeIs('articles.*')">
                        Articles
                    </x-nav-link>
                </li>
                <li>
                    <x-nav-link :href="route('trips.index')" :active="request()->routeIs('trips.*')">
                        Sorties
                    </x-nav-link>
                </li>
                <li>
                    <x-nav-link :href="route('events.index')" :active="request()->routeIs('events.*')">
                        Programme
                    </x-nav-link>
                </li>
                <li>
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false"
                        @close.stop="open = false">
                        <div class="flex items-center space-x-3" @click="open = ! open" :aria-expanded="open">
                            <x-nav-link id="gallery-menu-button" class="flex items-center w-full justify-between"
                                :active="request()->routeIs('photos.*') || request()->routeIs('albums.*')">
                                <span>Galerie</span>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="ml-1 h-4 w-4">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </x-nav-link>
                        </div>
                        <div x-show="open" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute z-50 mt-2 min-w-[10rem] w-full rounded-md shadow-lg origin-top-left left-0"
                            style="display: none;" @click="open = false">
                            <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white">
                                <ul class="py-1" aria-labelledby="gallery-menu-button">
                                    <li>
                                        <x-dropdown-link :href="route('albums.index')">
                                            Albums
                                        </x-dropdown-link>
                                        <x-dropdown-link :href="route('photos.index')">
                                            Toutes les photos
                                        </x-dropdown-link>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>

            {{-- // TODO Barre de recherche sorties/albums/photos --}}
        </div>
    </div>
</nav>
