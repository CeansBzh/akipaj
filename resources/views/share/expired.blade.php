{{-- Lien de partage expiré, ou dont la photo ou l'album a été supprimé --}}
<x-auth-layout title="Ce lien ne fonctionne plus">
    <x-slot:intro>
        Les liens de partage du club ne fonctionnent que quelques jours, ou la photo a été retirée depuis.
    </x-slot:intro>

    <p class="leading-relaxed text-gray-800">
        Demandez un nouveau lien à la personne qui vous l'a envoyé.
    </p>
    <p class="mt-4 leading-relaxed text-gray-800">
        Vous êtes membre du club ?
        <a href="{{ route('login') }}"
            class="text-primary-800 hover:text-primary-900 font-semibold underline underline-offset-4">Connectez-vous</a>
        pour retrouver toutes les photos dans la galerie.
    </p>
</x-auth-layout>
