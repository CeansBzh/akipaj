<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">Informations personnelles</h2>
        <p class="mt-1 text-sm text-gray-600">Renseigner ces informations est facultatif, elles ne sont utilisées qu'au
            sein de l'association.</p>
    </header>

    <form method="post" action="{{ route('settings.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="flex w-full flex-col space-y-6 sm:flex-row sm:space-y-0 sm:space-x-3">
            <div class="sm:w-1/2">
                <x-input-label for="lastname" value="Nom" />
                <x-text-input id="lastname" name="lastname" type="text" class="mt-1 block w-full" :value="old('lastname', $user->lastname)"
                    autocomplete="family-name" />
                <x-input-error class="mt-2" :messages="$errors->get('lastname')" />
            </div>
            <div class="sm:w-1/2">
                <x-input-label for="firstname" value="Prénom" />
                <x-text-input id="firstname" name="firstname" type="text" class="mt-1 block w-full" :value="old('firstname', $user->firstname)"
                    autocomplete="given-name" />
                <x-input-error class="mt-2" :messages="$errors->get('firstname')" />
            </div>
        </div>

        <div>
            <x-input-label for="birthdate" value="Date de naissance" />
            <x-text-input id="birthdate" name="birthdate" type="date" class="mt-1 block w-full" :value="old('birthdate', $user->birthdate) !== null
                ? Carbon\Carbon::parse(old('birthdate', $user->birthdate))->format('Y-m-d')
                : ''"
                max="{{ now()->format('Y-m-d') }}" autocomplete="bday" />
            <x-input-error class="mt-2" :messages="$errors->get('birthdate')" />
        </div>

        <div>
            <x-input-label for="clothing_size" value="Taille de vétements" />
            <x-select-input id="clothing_size" name="clothing_size" class="mt-1 block w-full" :value="old('clothing_size', $user->clothing_size)">
                <option {{ $user->clothing_size ? '' : 'selected' }}>--</option>
                @foreach (['XS', 'S', 'M', 'L', 'XL', 'XXL'] as $size)
                    <option {{ $size == $user->clothing_size ? 'selected' : '' }}>{{ $size }}</option>
                @endforeach
            </x-select-input>
            <x-input-error class="mt-2" :messages="$errors->get('address')" />
        </div>

        <div class="strike block overflow-hidden whitespace-nowrap text-center">
            <span class="relative inline-block text-sm text-gray-800">Contact</span>
        </div>

        <div>
            <x-input-label for="secondary_email" value="Adresse mail secondaire" />
            <x-text-input id="secondary_email" name="secondary_email" type="email" class="mt-1 block w-full"
                :value="old('secondary_email', $user->secondary_email)" />
            <x-input-error class="mt-2" :messages="$errors->get('secondary_email')" />
        </div>

        <div>
            <x-input-label for="mobile_phone" value="N° de téléphone portable" />
            <div class="relative">
                <p class="absolute top-[0.55rem] left-2 text-gray-600">+33</p>
                <x-text-input id="mobile_phone" name="mobile_phone" type="tel" class="mt-1 block w-full pl-11"
                    :value="old('mobile_phone', chunk_split($user->mobile_phone, 2, ' '))" autocomplete="tel" pattern="0[1-9](?: [0-9]{2}){4}"
                    title="Numéro de téléphone en format français, 10 chiffres de long" />
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('mobile_phone')" />
        </div>

        <div>
            <x-input-label for="home_phone" value="N° de téléphone fixe" />
            <div class="relative">
                <p class="absolute top-[0.55rem] left-2 text-gray-600">+33</p>
                <x-text-input id="home_phone" name="home_phone" type="tel" class="mt-1 block w-full pl-11"
                    :value="old('home_phone', chunk_split($user->home_phone, 2, ' '))" autocomplete="tel" pattern="0[1-9](?: [0-9]{2}){4}"
                    title="Numéro de téléphone en format français, 10 chiffres de long" />
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('home_phone')" />
        </div>

        <div class="strike block overflow-hidden whitespace-nowrap text-center">
            <span class="relative inline-block text-sm text-gray-800">Adresse</span>
        </div>

        <div>
            <x-input-label for="address" value="Adresse" />
            <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address', $user->address)"
                autocomplete="street-address" />
            <x-input-error class="mt-2" :messages="$errors->get('address')" />
        </div>

        <div>
            <x-input-label for="postal_code" value="Code" />
            <x-text-input id="postal_code" name="postal_code" type="number" class="mt-1 block w-full" min="1000"
                max="99999" :value="old('postal_code', $user->postal_code)" autocomplete="postal-code" />
            <x-input-error class="mt-2" :messages="$errors->get('postal_code')" />
        </div>

        <div>
            <x-input-label for="city" value="Ville" />
            <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city', $user->city)"
                autocomplete="address-level2" />
            <x-input-error class="mt-2" :messages="$errors->get('city')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Mettre à jour</x-primary-button>

            @if (session('status') === 'personal-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600">Enregistré.</p>
            @endif
        </div>
    </form>
</section>
@push('styles')
    <style>
        .strike>span:before,
        .strike>span:after {
            content: "";
            position: absolute;
            top: 50%;
            width: 9999px;
            height: 1px;
            background: rgb(209 213 219);
        }

        .strike>span:before {
            right: 100%;
            margin-right: 15px;
        }

        .strike>span:after {
            left: 100%;
            margin-left: 15px;
        }
    </style>
@endpush
@push('scripts')
    <script>
        function phone_number_format(value) {
            var v = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '')
            var matches = v.match(/\d{2,10}/g);
            var match = matches && matches[0] || ''
            var parts = []
            for (i = 0, len = match.length; i < len; i += 2) {
                parts.push(match.substring(i, i + 2))
            }
            if (parts.length) {
                return parts.join(' ')
            } else {
                return value
            }
        }

        onload = function() {
            document.getElementById('mobile_phone').oninput = function() {
                this.value = this.value.replace(/\D+/g, '');
                this.value = phone_number_format(this.value);
            }

            document.getElementById('home_phone').oninput = function() {
                this.value = this.value.replace(/\D+/g, '');
                this.value = phone_number_format(this.value);
            }
        }
    </script>
@endpush
