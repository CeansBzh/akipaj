<x-auth-layout title="Créer un compte">
    <x-slot name="intro">Rejoignez l'équipage pour suivre les sorties du club et partager vos photos.</x-slot>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="name" value="Pseudo" />
            <x-text-input id="name" class="mt-1.5 block w-full" type="text" name="name" :value="old('name')"
                required autofocus autocomplete="nickname" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="Adresse e-mail" />
            <x-text-input id="email" class="mt-1.5 block w-full" type="email" name="email" :value="old('email')"
                required autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Mot de passe" />
            <x-text-input id="password" class="mt-1.5 block w-full" type="password" name="password" required
                autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirmer le mot de passe" />
            <x-text-input id="password_confirmation" class="mt-1.5 block w-full" type="password"
                name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full">Créer mon compte</x-primary-button>
    </form>

    <x-slot name="footer">
        Déjà membre ?
        <a href="{{ route('login') }}"
            class="text-primary-800 decoration-primary-300 hover:decoration-primary-700 focus-visible:outline-primary-700 rounded-sm font-semibold underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2">Se
            connecter</a>
    </x-slot>
</x-auth-layout>
