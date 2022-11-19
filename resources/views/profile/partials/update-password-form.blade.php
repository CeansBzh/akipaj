<section x-data="app()">
    <header>
        <h2 class="text-lg font-medium text-gray-900">Changer de mot de passe</h2>
        <p class="mt-1 text-sm text-gray-600">Assurez-vous d'utiliser un mot de passe long et complexe pour protéger votre compte.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div class="relative">
            <x-input-label for="current_password" value="Mot de passe actuel" />
            <x-text-input id="current_password" name="current_password" ::type="showOldPasswordField?'password':'text'"
                class="mt-1 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />

            <button
                class="block w-7 h-7 text-center text-xl leading-0 absolute rounded top-8 right-2 text-gray-400 focus:outline-none hover:text-sky-500 focus:text-sky-500 focus:motion-safe:animate-pulse transition-colors"
                @click.prevent="showOldPasswordField=!showOldPasswordField">
                <span x-show="showOldPasswordField">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="m-0">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </span>
                <span x-show="!showOldPasswordField">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="m-0">
                        <path
                            d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24">
                        </path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                </span>
            </button>
        </div>

        <div class="relative">
            <x-input-label for="password" value="Nouveau mot de passe" />
            <x-text-input id="password" name="password" ::type="showNewPasswordField?'password':'text'" class="mt-1 block w-full"
                autocomplete="new-password" x-model="password" @input="checkStrength()" />
            <div class="flex -mx-1 mt-2 mb-4">
                <template x-for="(v,i) in 5">
                    <div class="w-1/5 px-1">
                        <div class="h-2 rounded-xl transition-colors"
                            :class="i<passwordScore?(passwordScore<=2?'bg-red-400':(passwordScore<=4?'bg-yellow-400':'bg-green-500')):'bg-gray-200'">
                        </div>
                    </div>
                </template>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />

            <button
                class="block w-7 h-7 text-center text-xl leading-0 absolute top-8 right-2 text-gray-400 focus:outline-none hover:text-sky-500 focus:text-sky-500 focus:motion-safe:animate-pulse transition-colors"
                @click.prevent="showNewPasswordField=!showNewPasswordField">
                <span x-show="showNewPasswordField">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="feather feather-eye">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </span>
                <span x-show="!showNewPasswordField">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="feather feather-eye-off">
                        <path
                            d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24">
                        </path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                </span>
            </button>
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirmer le mot de passe" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Modifier</x-primary-button>

            @if (session('status') === 'password-updated')
            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                class="text-sm text-gray-600">Enregistré.</p>
            @endif
        </div>
    </form>
</section>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js"></script>
<script>
    function app() {
        return {
            showOldPasswordField: true,
            showNewPasswordField: true,
            passwordScore: 0,
            password: '',
            chars: {
                lower: 'abcdefghijklmnopqrstuvwxyz',
                upper: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                numeric: '0123456789',
                symbols: '!"#$%&\'()*+,-./:;<=>?@[\\]^_`{|}~'
            },
            charsLength: 12,
            checkStrength: function () {
                if (!this.password) return this.passwordScore = 0;
                this.passwordScore = zxcvbn(this.password).score + 1;
            },
        }
    }
</script>
@endpush