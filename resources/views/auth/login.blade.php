<x-auth-layout title="Connexion">
    <x-slot name="intro">Connectez-vous pour voir les prochaines sorties et les photos du club.</x-slot>

    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" value="Adresse e-mail" />
            <x-text-input id="email" class="mt-1.5 block w-full" type="email" name="email" :value="old('email')"
                required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Mot de passe" />
            <x-text-input id="password" class="mt-1.5 block w-full" type="password" name="password" required
                autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
            @if (Route::has('password.request'))
                <p class="mt-2 text-right">
                    <a href="{{ route('password.request') }}"
                        class="text-primary-800 decoration-primary-300 hover:decoration-primary-700 focus-visible:outline-primary-700 rounded-sm font-semibold underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2">Mot
                        de passe oublié ?</a>
                </p>
            @endif
        </div>

        <label for="remember_me" class="-mx-2 flex min-h-11 cursor-pointer items-center gap-3 rounded-xl px-2">
            <input id="remember_me" type="checkbox" name="remember"
                class="text-primary-700 focus:ring-primary-600 size-5 rounded-md border-gray-400">
            <span class="text-gray-800">Rester connecté sur cet appareil</span>
        </label>

        <x-primary-button class="w-full">Se connecter</x-primary-button>
    </form>

    <x-slot name="footer">
        Pas encore de compte ?
        <a href="{{ route('register') }}"
            class="text-primary-800 decoration-primary-300 hover:decoration-primary-700 focus-visible:outline-primary-700 rounded-sm font-semibold underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2">Créer
            un compte</a>
    </x-slot>
</x-auth-layout>
