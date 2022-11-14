<div class="max-w-lg mx-auto" x-data="app()">
    <form method="POST" action="{{ route('password.change') }}">
        @csrf
        <p class="font-semibold text-gray-600 mb-3">Changer de mot de passe</p>
        <div class="mb-2">
            <div class="relative">
                <x-input-label for="oldPassword" value="Mot de passe actuel" />
                <x-text-input ::type="showOldPasswordField?'password':'text'" id="oldPassword" class="block mt-1 w-full"
                    name="oldPassword" required autocomplete="current-password" />
                <button
                    class="block w-7 h-7 text-center text-xl leading-0 absolute rounded bottom-2 right-2 text-gray-400 focus:outline-none hover:text-sky-500 focus:text-sky-500 focus:motion-safe:animate-pulse transition-colors"
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
            <x-input-error :messages="$errors->get('oldPassword')" class="mt-2" />
        </div>
        <div class="mb-2">
            <div class="relative">
                <x-input-label for="newPassword" value="Nouveau mot de passe" />
                <x-text-input ::type="showNewPasswordField?'password':'text'" id="newPassword" class="block mt-1 w-full"
                    name="newPassword" x-model="password" required autocomplete="current-password"
                    @input="checkStrength()" />
                <button
                    class="block w-7 h-7 text-center text-xl leading-0 absolute bottom-2 right-2 text-gray-400 focus:outline-none hover:text-sky-500 focus:text-sky-500 focus:motion-safe:animate-pulse transition-colors"
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
            <x-input-error :messages="$errors->get('newPassword')" class="mt-2" />
        </div>
        <div class="flex -mx-1 mb-4">
            <template x-for="(v,i) in 5">
                <div class="w-1/5 px-1">
                    <div class="h-2 rounded-xl transition-colors"
                        :class="i<passwordScore?(passwordScore<=2?'bg-red-400':(passwordScore<=4?'bg-yellow-400':'bg-green-500')):'bg-gray-200'">
                    </div>
                </div>
            </template>
        </div>
        <div class="mb-4">
            <x-input-label for="newPassword-confirmation" value="Confirmation mot de passe" />
            <x-text-input id="newPassword-confirmation" class="block mt-1 w-full" type="password"
                name="newPassword_confirmation" required />
        </div>
        <div class="flex justify-end mt-4">
            <x-primary-button>
                {{ __('Confirm') }}
            </x-primary-button>
        </div>
    </form>
</div>

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