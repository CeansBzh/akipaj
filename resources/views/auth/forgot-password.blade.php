<x-auth-layout title="Mot de passe oublié">
    <x-slot name="intro">Indiquez votre adresse e-mail : nous vous enverrons un lien pour choisir un nouveau mot de
        passe.</x-slot>

    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" value="Adresse e-mail" />
            <x-text-input id="email" class="mt-1.5 block w-full" type="email" name="email" :value="old('email')"
                required autofocus autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-primary-button class="w-full">Recevoir le lien par e-mail</x-primary-button>
    </form>

    <x-slot name="footer">
        Vous l'avez retrouvé ?
        <a href="{{ route('login') }}"
            class="text-primary-800 decoration-primary-300 hover:decoration-primary-700 focus-visible:outline-primary-700 rounded-sm font-semibold underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2">Se
            connecter</a>
    </x-slot>
</x-auth-layout>
