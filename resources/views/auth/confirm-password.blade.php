<x-auth-layout title="Confirmez votre mot de passe">
    <x-slot name="intro">Cette partie du site est protégée : saisissez de nouveau votre mot de passe pour
        continuer.</x-slot>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="password" value="Mot de passe" />
            <x-text-input id="password" class="mt-1.5 block w-full" type="password" name="password" required autofocus
                autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <x-primary-button class="w-full">Continuer</x-primary-button>
    </form>
</x-auth-layout>
