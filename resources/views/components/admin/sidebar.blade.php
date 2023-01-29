<aside class="relative hidden h-screen w-64 bg-sky-700 shadow-xl sm:block">
    <div class="p-6">
        <a class="text-3xl font-semibold uppercase text-white hover:text-gray-300" href="{{ url('/') }}">Akipaj</a>
    </div>
    <nav class="pt-3 text-base font-semibold text-white">
        <x-admin.sidebar-link :href="route('admin.index')" :active="request()->routeIs('admin.index')">
            Panneau d'accueil
        </x-admin.sidebar-link>
        <x-admin.sidebar-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.index')">
            Utilisateurs
        </x-admin.sidebar-link>
        <x-admin.sidebar-link class="mt-2 border-t-2" :href="url('/')">
            Retour au site
        </x-admin.sidebar-link>
    </nav>
</aside>
