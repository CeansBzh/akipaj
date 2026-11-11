<x-auth-layout title="Vérifiez votre adresse e-mail">
    <x-slot name="intro">Merci pour votre inscription ! Ouvrez le lien que nous venons de vous envoyer par e-mail
        pour confirmer votre adresse.</x-slot>

    @if (session('status') == 'verification-link-sent')
        <x-auth-session-status class="mb-6"
            status="Un nouveau lien vient d'être envoyé à l'adresse e-mail de votre inscription." />
    @endif

    <p class="leading-relaxed text-gray-700">Vous n'avez rien reçu ? Pensez à regarder dans les courriers indésirables,
        ou demandez un nouveau lien.</p>

    <form method="POST" action="{{ route('verification.send') }}" class="mt-5">
        @csrf
        <x-primary-button class="w-full">Recevoir un nouveau lien</x-primary-button>
    </form>

    <x-slot name="footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="text-primary-800 decoration-primary-300 hover:decoration-primary-700 focus-visible:outline-primary-700 rounded-sm font-semibold underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2">Se
                déconnecter</button>
        </form>
    </x-slot>
</x-auth-layout>
