<section>
    <form method="post" action="{{ route('payments.store') }}">
        @csrf
        <div x-data="app()" x-cloak>
            <div class="max-w-xl mx-auto px-4 py-2 flex items-center justify-center">

                {{-- Error modal --}}
                <x-modal name="photo-create-errors" :show="$errors->isNotEmpty()">
                    <div class="p-10 flex text-center">
                        <div class="mx-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" fill="currentColor"
                                class="mb-4 h-16 w-16 text-red-500 mx-auto">
                                <g transform="matrix(.99999 0 0 .99999-58.37.882)">
                                    <circle cx="82.37" cy="23.12" r="24" />
                                    <path
                                        d="m87.77 23.725l5.939-5.939c.377-.372.566-.835.566-1.373 0-.54-.189-.997-.566-1.374l-2.747-2.747c-.377-.372-.835-.564-1.373-.564-.539 0-.997.186-1.374.564l-5.939 5.939-5.939-5.939c-.377-.372-.835-.564-1.374-.564-.539 0-.997.186-1.374.564l-2.748 2.747c-.377.378-.566.835-.566 1.374 0 .54.188.997.566 1.373l5.939 5.939-5.939 5.94c-.377.372-.566.835-.566 1.373 0 .54.188.997.566 1.373l2.748 2.747c.377.378.835.564 1.374.564.539 0 .997-.186 1.374-.564l5.939-5.939 5.94 5.939c.377.378.835.564 1.374.564.539 0 .997-.186 1.373-.564l2.747-2.747c.377-.372.566-.835.566-1.373 0-.54-.188-.997-.566-1.373l-5.939-5.94"
                                        fill="white" />
                                </g>
                            </svg>

                            <h2 class="text-2xl mb-4 text-gray-800 font-bold">Erreurs lors de
                                l'envoi</h2>

                            <ul class="text-gray-600 text-sm mb-5 list-decimal">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </x-modal>

                {{-- Success modal --}}
                <x-modal name="photo-create-errors" :show="session('success')">
                    <div class="p-10 flex text-center">
                        <div class="mx-auto">
                            <svg class="mb-4 h-20 w-20 text-green-500 mx-auto" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            <h2 class="text-2xl mb-4 text-gray-800 font-bold">Paiement envoyé avec succès</h2>
                            <div class="text-gray-600 mb-8">
                                Vous pouvez retrouvez l'historique de vos paiements sur votre <a
                                    href="{{ route('profile.index') }}"
                                    class="text-blue-500 hover:text-blue-700">profil</a>.
                            </div>
                        </div>
                    </div>
                </x-modal>

                <div class="w-full md:min-w-[30vw]">
                    {{-- Top nav --}}
                    <div class="border-b-2 py-2 flex">
                        <div class="uppercase tracking-wide text-xs font-bold text-sky-500 mr-2 my-auto leading-tight"
                            x-text="`Étape ${step}`"></div>
                        <div x-show="step === 1">
                            <div class="text-lg font-bold text-gray-700 leading-tight">Choisir le montant</div>
                        </div>
                        <div x-show="step === 2">
                            <div class="text-lg font-bold text-gray-700 leading-tight">Informations de paiement
                            </div>
                        </div>
                    </div>

                    {{-- Step content --}}
                    <div class="py-8">
                        {{-- Step 1 --}}
                        <div x-show.transition.in="step === 1">
                            <div class="mb-5">
                                <p class="text-lg font-bold">Je fais un virement de</p>
                                <div class="py-5">
                                    <div class="flex flex-row text-gray-700">
                                        <div class="w-fit">
                                            <input id="amount" type="text" name="amount" x-model="amount"
                                                @input="updateAmount"
                                                class="p-0 text-5xl mx-auto w-[3.5ch] min-w-[3.5ch] placeholder:text-gray-400 border-0 border-b-4 border-gray-400 focus:border-sky-500 focus:ring-0"
                                                placeholder="0,00">
                                        </div>
                                        <p class="text-5xl self-center select-none">€</p>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-400">Montant maximum 999,99€</p>
                                </div>
                                <p class="text-lg font-bold">à l'association Akipaj.</p>
                                <div class="py-4">
                                    <x-input-label for="message" value="Inclure un message personnalisé" />
                                    <x-text-input id="message" type="text" name="message" class="mt-1 block w-full"
                                        maxlength="256" placeholder="Virement de {{ Auth::user()->name }} à Akipaj" />
                                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                                </div>
                            </div>
                        </div>
                        {{-- Step 2 --}}
                        <div x-show.transition.in="step === 2">
                            <div id="card_form" class="flex flex-col space-y-4">
                                <div class="relative">
                                    <x-input-label for="card_number" value="Numéro de carte" />
                                    <x-text-input id="card_number" dir="ltr" type="text" inputmode="numeric"
                                        name="card_number" class="mt-1 block w-full" placeholder="1234 1234 1234 1234"
                                        autocomplete="billing cc-number" aria-invalid="false" zzRequired />
                                    <x-input-error :messages="$errors->get('card_number')" class="mt-2" />
                                    <img id="card_number_logo"
                                        src="{{ Vite::asset('resources/views/payment/images/credit-card.svg') }}"
                                        class="absolute top-[2.2rem] right-2 h-5" alt="Logo marque carte de crédit">
                                </div>

                                <div class="flex space-x-3">
                                    <div class="flex-1">
                                        <x-input-label for="card_expiry" class="whitespace-nowrap"
                                            value="Date d'expiration" />
                                        <x-text-input id="card_expiry" dir="ltr" type="text" inputmode="numeric"
                                            name="card_expiry" class="mt-1 block w-full" placeholder="MM / YY"
                                            autocomplete="billing cc-exp" aria-invalid="false" zzRequired />
                                        <x-input-error :messages="$errors->get('card_expiry')" class="mt-2" />
                                    </div>
                                    <div class="flex-1">
                                        <x-input-label for="card_cvc" value="CVC" />
                                        <x-text-input id="card_cvc" dir="ltr" type="text" inputmode="numeric"
                                            name="card_cvc" maxlength="4" class="mt-1 block w-full" placeholder="CVC"
                                            autocomplete="billing cc-csc" aria-invalid="false" zzRequired />
                                        <x-input-error :messages="$errors->get('card_cvc')" class="mt-2" />
                                    </div>
                                </div>

                                <div>
                                    <x-input-label for="card_holder_name" value="Nom du titulaire de la carte" />
                                    <x-text-input id="card_holder_name" dir="ltr" type="text" name="card_holder_name"
                                        maxlength="128" class="mt-1 block w-full" placeholder="Prénom Nom"
                                        autocomplete="billing cc-name" aria-invalid="false" zzRequired />
                                    <x-input-error :messages="$errors->get('card_holder_name')" class="mt-2" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bottom Navigation --}}
            <div class="fixed bottom-0 left-0 right-0">
                <div class="max-w-3xl mx-auto h-20 p-5 bg-white rounded-t-xl drop-shadow-[0_-6px_6px_rgba(0,0,0,0.1)]">
                    <div class="flex justify-between">
                        <div class="mr-2">
                            <x-secondary-button x-show="step == 1" @click="history.back()">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="sm:hidden h-4"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="feather feather-x">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                                <span class="hidden sm:block">Annuler</span>
                            </x-secondary-button>
                            <x-secondary-button x-show="step > 1" @click="step--">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="sm:hidden h-4"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <line x1="19" y1="12" x2="5" y2="12"></line>
                                    <polyline points="12 19 5 12 12 5"></polyline>
                                </svg>
                                <span class="hidden sm:block">Retour</span>
                            </x-secondary-button>
                        </div>
                        <div id="next-step" class="grow text-right">
                            <x-primary-button type="button" x-show="step < 2" @click="step++">Étape suivante
                                </x-secondary-button>
                                <x-primary-button x-show="step === 2"
                                    x-text="amount != '' ? 'Envoyer ' + amount.replace(/(^,)|(,$)/g, '') + '€':'Montant invalide'"
                                    x-bind:disabled="amount == ''">Montant invalide</x-primary-button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>

@push('scripts')
<script type="text/javascript">
    function app() {
        return {
            amount: '',
            step: 1,
            updateAmount: function () {
                // L'utilisateur ne peut entrer qu'un string correspondant au regex
                this.amount = this.amount.match(/([1-9]\d{0,2}(?:,\d{0,2})?)|(?:(?![1-9]\d{0,2}(?:,\d{0,2})?).)*/)[1] || '';
                if (this.amount.length > 3) {
                    document.getElementById('amount').style.width = (this.amount.length - 0.5) + 'ch';
                } else {
                    document.getElementById('amount').style.width = '3ch';
                }
            },
        }
    }

    function cc_format(value) {
        var v = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '')
        var matches = v.match(/\d{4,16}/g);
        var match = matches && matches[0] || ''
        var parts = []
        for (i = 0, len = match.length; i < len; i += 4) {
            parts.push(match.substring(i, i + 4))
        }
        if (parts.length) {
            updateCardLogo(parts.join(''))
            return parts.join(' ')
        } else {
            return value
        }
    }

    function expiry_format(value) {
        var v = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '')
        var matches = v.match(/\d{2,4}/g);
        var match = matches && matches[0] || ''
        var parts = []
        for (i = 0, len = match.length; i < len; i += 2) {
            parts.push(match.substring(i, i + 2))
        }
        if (parts.length) {
            return parts.join(' / ')
        } else {
            return value
        }
    }

    function creditCardType(cc) {
        let amex = new RegExp('^3[47][0-9]{13}$');
        let visa = new RegExp('^4[0-9]{12}(?:[0-9]{3})?$');
        let cup1 = new RegExp('^62[0-9]{14}[0-9]*$');
        let cup2 = new RegExp('^81[0-9]{14}[0-9]*$');

        let mastercard = new RegExp('^5[1-5][0-9]{14}$');
        let mastercard2 = new RegExp('^2[2-7][0-9]{14}$');

        let disco1 = new RegExp('^6011[0-9]{12}[0-9]*$');
        let disco2 = new RegExp('^62[24568][0-9]{13}[0-9]*$');
        let disco3 = new RegExp('^6[45][0-9]{14}[0-9]*$');

        let diners = new RegExp('^3[0689][0-9]{12}[0-9]*$');
        let jcb = new RegExp('^35[0-9]{14}[0-9]*$');


        if (visa.test(cc)) {
            return 'VISA';
        }
        if (amex.test(cc)) {
            return 'AMEX';
        }
        if (mastercard.test(cc) || mastercard2.test(cc)) {
            return 'MASTERCARD';
        }
        if (disco1.test(cc) || disco2.test(cc) || disco3.test(cc)) {
            return 'DISCOVER';
        }
        if (diners.test(cc)) {
            return 'DINERS';
        }
        if (jcb.test(cc)) {
            return 'JCB';
        }
        if (cup1.test(cc) || cup2.test(cc)) {
            return 'UNION_PAY';
        }
        return undefined;
    }

    function updateCardLogo(cc) {
        let cardNumberInput = document.getElementById('card_number');
        let cardLogo = document.getElementById('card_number_logo');
        let cardType = creditCardType(cc);

        cardNumberInput.classList.remove('border-red-500', 'focus:border-red-600', 'focus:ring-red-300');
        cardNumberInput.classList.add('border-gray-300', 'focus:border-sky-500', 'focus:ring-sky-500');
        document.getElementById('card_number_error')?.remove();

        if (cardType === 'VISA') {
            cardLogo.src = "{{ Vite::asset('resources/views/payment/images/logo-visa.svg') }}";
        } else if (cardType === 'AMEX') {
            cardLogo.src = "{{ Vite::asset('resources/views/payment/images/logo-amex.svg') }}";
        } else if (cardType === 'MASTERCARD') {
            cardLogo.src = "{{ Vite::asset('resources/views/payment/images/logo-mastercard.svg') }}";
        } else if (cardType === 'DISCOVER') {
            cardLogo.src = "{{ Vite::asset('resources/views/payment/images/logo-discover.svg') }}";
        } else if (cardType === 'DINERS') {
            cardLogo.src = "{{ Vite::asset('resources/views/payment/images/logo-diners.svg') }}";
        } else if (cardType === 'JCB') {
            cardLogo.src = "{{ Vite::asset('resources/views/payment/images/logo-jcb.svg') }}";
        } else if (cardType === 'UNION_PAY') {
            cardLogo.src = "{{ Vite::asset('resources/views/payment/images/logo-union-pay.svg') }}";
        } else {
            cardLogo.src = "{{ Vite::asset('resources/views/payment/images/credit-card.svg') }}";
        }
    }

    function checkIfValid(value) {
        var v = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '')

        if (v.length > 0 && creditCardType(v) === undefined) {
            let cardLogo = document.getElementById('card_number_logo');
            let cardNumberInput = document.getElementById('card_number');

            cardNumberInput.classList.remove('border-gray-300', 'focus:border-sky-500', 'focus:ring-sky-500');
            cardNumberInput.classList.add('border-red-500', 'focus:border-red-600', 'focus:ring-red-300');
            cardLogo.src = "{{ Vite::asset('resources/views/payment/images/alert-circle.svg') }}";

            if (document.getElementById('card_number_error') == null) {
                let cardNumberError = document.createElement('p');
                cardNumberError.classList.add('text-sm', 'text-red-600');
                cardNumberError.id = 'card_number_error';
                cardNumberError.innerHTML = 'Numéro de carte invalide.';
                cardNumberInput.parentNode.insertBefore(cardNumberError, cardNumberInput.nextSibling);
            }
        }
    }

    onload = function () {
        document.getElementById('card_number').oninput = function () {
            this.value = this.value.replace(/\D+/g, '');
            this.value = cc_format(this.value);
        }

        document.getElementById('card_number').onblur = function () {
            checkIfValid(this.value);
        }

        document.getElementById('card_expiry').oninput = function () {
            this.value = this.value.replace(/\D+/g, '');
            this.value = expiry_format(this.value);
        }

        document.getElementById('card_cvc').oninput = function () {
            this.value = this.value.replace(/\D+/g, '');
        }
    }
</script>
@endpush