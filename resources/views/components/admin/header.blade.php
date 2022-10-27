<!-- Desktop Header -->
<header class="w-full items-center bg-white py-2 px-6 hidden sm:flex sm:flex-row-reverse">
    <div class="hidden py-2 sm:flex sm:items-center sm:ml-5">
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <img class="w-8 h-8 rounded-full"
                    src="https://upload.wikimedia.org/wikipedia/commons/thumb/7/7e/Circle-icons-profile.svg/512px-Circle-icons-profile.svg.png"
                    alt="Photo de profil">
                <button
                    class="flex items-center font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                    {{ Auth::user()->name }}

                    <div class="ml-1">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                </button>
            </x-slot>

            <x-slot name="content">
                {{-- Authentication --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-dropdown-link :href="route('logout')" onclick="event.preventDefault();
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
        <a href="index.html" class="text-white text-3xl font-semibold uppercase hover:text-gray-300">Akipaj</a>
        <button @click="open = !open"
            class="p-1 rounded-md text-white hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
            <svg class="h-8 w-8" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round"
                    stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                    stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Dropdown Nav -->
    <nav :class="open ? 'flex': 'hidden'" class="flex flex-col pt-4">
        <x-admin.responsive-header-link :href="route('admin.index')" :active="request()->routeIs('admin.index')">
            Panneau d'accueil
        </x-admin.responsive-header-link>
        <x-admin.responsive-header-link :href="route('admin.users')" :active="request()->routeIs('admin.users')">
            Utilisateurs
        </x-admin.responsive-header-link>
        <x-admin.responsive-header-link href="#" :active="false">
            Mon profil
        </x-admin.responsive-header-link>
        {{-- Authentication --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-admin.responsive-header-link :href="route('logout')" onclick="event.preventDefault();
                                this.closest('form').submit();">
                {{ __('Déconnexion') }}
            </x-admin.responsive-header-link>
        </form>
    </nav>
</header>