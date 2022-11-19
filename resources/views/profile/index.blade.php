<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Mon profil
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto flex-col space-y-4 sm:px-6 lg:px-8">
            @if(auth()->user()->hasRole('guest'))
            <div x-data="{ animation: false }" class="px-1">
                <div role="alert"
                    class="p-4 mb-6 text-sm text-yellow-800 bg-yellow-200 rounded-lg duration-500 relative transition ease-in-out scale-90 md:scale-100"
                    :class="animation ? '-translate-y-1 scale-100 ring-offset-2 ring ring-yellow-300' : ''"
                    x-init="$nextTick(() => {animation = true; setTimeout(() => { animation = false; }, 500);})">
                    Votre inscription n'a pas encore été validée par un administrateur. Vous ne pouvez pas encore
                    accéder à toutes les pages du site.
                </div>
            </div>
            @endif

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <section class="flex flex-col justify-around space-y-4 xs:space-y-0 xs:flex-row">
                    <header class="flex items-center mx-auto sm:mx-0">
                        <h2 class="text-lg font-medium text-gray-900">Modifier le profil</h2>
                    </header>
                    <a href="{{ route('profile.edit') }}"
                        class="inline-flex items-center px-4 py-2 mx-auto sm:mx-0 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Paramètres
                    </a>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>