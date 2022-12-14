<x-member-layout>
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
                <div class="max-w-3xl mx-auto">
                    <section class="flex flex-col justify-between items-center space-y-4 xs:space-y-0 xs:flex-row">
                        <h2 class="text-lg font-medium text-gray-900">Modifier le profil</h2>
                        <x-primary-link class="" href="{{ route('profile.edit') }}">
                            Paramètres
                        </x-primary-link>
                    </section>
                </div>
            </div>

            @if(isset($latestPayments))
            <hr>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-3xl mx-auto">
                    @include('profile.partials.latest-payments')
                </div>
            </div>
            @endif
        </div>
    </div>
</x-member-layout>
