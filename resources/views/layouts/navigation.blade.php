<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    {{-- Primary Navigation Menu --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-2 md:px-6 lg:px-8">
        <div class="flex justify-center h-24">
            <div class="flex justify-between w-screen items-center">
                {{-- First set of navigation links --}}
                <div class="hidden h-full w-full space-x-4 sm:-my-px sm:flex md:space-x-8 md:mr-10">
                    <x-nav-link class="w-1/3" :href="url('/')" :active="(url()->current() == url('/'))">
                        {{ __('Accueil') }}
                    </x-nav-link>
                    <x-nav-link class="w-1/3" :href="route('trips.index')" :active="request()->routeIs('trips.*')">
                        Sorties
                    </x-nav-link>
                    <x-nav-link class="w-1/3" href="#" :active="false">
                        {{ __('Actualités') }}
                    </x-nav-link>
                </div>

                {{-- Logo --}}
                <div class="flex items-center mx-2 sm:grow">
                    <a class="mx-auto contents sm:block sm:space-x-0" href="{{ url('/') }}">
                        <x-application-logo class="block h-10 w-auto fill-current text-gray-600" />
                    </a>
                </div>

                {{-- Second set of navigation links --}}
                <div class="hidden h-full w-full space-x-4 sm:-my-px sm:flex md:space-x-8 md:ml-10">
                    <x-nav-dropdown class="w-1/3"
                        :active="request()->routeIs('photos.*') || request()->routeIs('albums.*')">
                        <x-slot name="name">Galerie</x-slot>
                        <x-slot name="children">
                            <x-responsive-nav-link class="pr-16" :href="route('photos.index')"
                                :active="request()->routeIs('photos.*')">
                                {{ __('Voir les photos') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('albums.index')"
                                :active="request()->routeIs('albums.*')">
                                {{ __('Voir les albums') }}
                            </x-responsive-nav-link>
                        </x-slot>
                        </x-nav-link-parent>
                        <x-nav-link class="w-1/3" :href="route('events.index')"
                            :active="request()->routeIs('events.*')">
                            Programme
                        </x-nav-link>
                        <x-nav-link class="w-1/3" href="#" :active="false">
                            {{ __('L\'asso') }}
                        </x-nav-link>
                </div>
            </div>

            {{-- Settings dropdown --}}
            @if(Auth::user() != null)
            <div class="hidden sm:flex sm:items-center sm:ml-5">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <img class="w-8 h-8 rounded-full"
                            src="https://upload.wikimedia.org/wikipedia/commons/thumb/7/7e/Circle-icons-profile.svg/512px-Circle-icons-profile.svg.png"
                            alt="Photo de profil">
                        <button
                            class="flex items-center font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                            {{ Auth::user()->name }}

                            <div class="ml-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.index')">
                            {{ __('Mon profil') }}
                        </x-dropdown-link>
                        @if(Auth::user()->hasRole('admin'))
                        <x-dropdown-link :href="route('admin.index')">
                            {{ __('Panneau admin') }}
                        </x-dropdown-link>
                        @endif
                        {{-- Authentication --}}
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Déconnexion') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
            @endif

            {{-- Hamburger --}}
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = !open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Responsive Navigation Menu --}}
    <div :class="{'block': open, 'hidden': !open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="url('/')" :active="(url()->current() == url('/'))">
                Accueil
            </x-responsive-nav-link>
            <x-responsive-nav-dropdown :active="request()->routeIs('photos.*') || request()->routeIs('albums.*')">
                <x-slot name="name">Galerie</x-slot>
                <x-slot name="children">
                    <x-responsive-nav-link :href="route('photos.index')" :active="request()->routeIs('photos.index')">
                        {{ __('Voir les photos') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('albums.index')" :active="request()->routeIs('albums.index')">
                        {{ __('Voir les albums') }}
                    </x-responsive-nav-link>
                </x-slot>
            </x-responsive-nav-dropdown>
            <x-responsive-nav-link :href="route('trips.index')" :active="request()->routeIs('trips.*')">
                Sorties
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('events.index')" :active="request()->routeIs('events.*')">
                Programme
            </x-responsive-nav-link>
        </div>

        {{-- Responsive Settings Options --}}
        @if(Auth::user() != null)
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.index')">
                    {{ __('Mon profil') }}
                </x-responsive-nav-link>
                @if(Auth::user()->hasRole('admin'))
                <x-responsive-nav-link :href="route('admin.index')">
                    {{ __('Panneau admin') }}
                </x-responsive-nav-link>
                @endif
                {{-- Authentication --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Déconnexion') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
        @endif
    </div>
</nav>