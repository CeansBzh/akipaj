<x-member-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Mon profil
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl flex-col space-y-4 sm:px-6 lg:px-8">
            @if (auth()->user()->hasRole('guest'))
                <div x-data="{ animation: false }" class="px-1">
                    <div role="alert"
                        class="relative mb-6 scale-90 rounded-lg bg-yellow-200 p-4 text-sm text-yellow-800 transition duration-500 ease-in-out md:scale-100"
                        :class="animation ? '-translate-y-1 scale-100 ring-offset-2 ring ring-yellow-300' : ''"
                        x-init="$nextTick(() => {
                            animation = true;
                            setTimeout(() => { animation = false; }, 500);
                        })">
                        Votre inscription n'a pas encore été validée par un administrateur. Vous ne pouvez pas encore
                        accéder à toutes les pages du site.
                    </div>
                </div>
            @endif

            <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                <div class="mx-auto max-w-3xl">
                    <section class="flex flex-col items-center justify-between space-y-4 xs:flex-row xs:space-y-0">
                        <h2 class="text-lg font-medium text-gray-900">Gestion du compte</h2>
                        <x-primary-link class="" href="{{ route('profile.edit') }}">
                            Paramètres
                        </x-primary-link>
                    </section>
                </div>
            </div>

            @if (isset($latestPayments))
                <hr>

                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <div class="mx-auto max-w-3xl">
                        @include('profile.partials.latest-payments')
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-member-layout>
