<!-- Desktop Header -->
<header class="hidden w-full items-center bg-white py-2 px-6 sm:flex sm:flex-row-reverse">
    <div class="hidden py-2 sm:ml-5 sm:flex sm:items-center">
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <img class="h-8 w-8 rounded-full"
                    src="{{ Auth::user()->profile_picture_path ?? Vite::asset('resources/images/default-pfp.png') }}"
                    alt="Photo de profil">
                <button
                    class="flex items-center font-medium text-gray-500 transition duration-150 ease-in-out hover:border-gray-300 hover:text-gray-700 focus:border-gray-300 focus:text-gray-700 focus:outline-none">
                    {{ Auth::user()->name }}

                    <div class="ml-1">
                        <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                </button>
            </x-slot>

            <x-slot name="content">
                <x-dropdown-link :href="route('profile.show', Auth::user()->name)">
                    Mon profil
                </x-dropdown-link>
                {{-- Authentication --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-dropdown-link :href="route('logout')"
                        onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        Déconnexion
                    </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</header>

<!-- Mobile Header & Nav -->
<header x-data="{ open: false }" class="w-full bg-sky-700 py-5 px-6 sm:hidden">
    <div class="flex items-center justify-between">
        <a href="{{ url('/') }}" class="text-3xl font-semibold uppercase text-white hover:text-gray-300">Akipaj</a>
        <button @click="open = !open"
            class="rounded-md p-1 text-white transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none">
            <svg class="h-8 w-8" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round"
                    stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                    stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Dropdown Nav -->
    <nav :class="open ? 'flex' : 'hidden'" class="flex flex-col pt-4">
        <x-admin.responsive-header-link :href="route('admin.index')" :active="request()->routeIs('admin.index')">
            Panneau d'accueil
        </x-admin.responsive-header-link>
        <x-admin.responsive-header-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.index')">
            Utilisateurs
        </x-admin.responsive-header-link>
        <x-admin.responsive-header-link :href="route('profile.show', Auth::user()->name)" :active="request()->routeIs('profile.*')">
            Mon profil
        </x-admin.responsive-header-link>
        {{-- Authentication --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-admin.responsive-header-link :href="route('logout')"
                onclick="event.preventDefault();
                                this.closest('form').submit();">
                {{ __('Déconnexion') }}
            </x-admin.responsive-header-link>
        </form>
    </nav>
</header>
