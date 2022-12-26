<section x-data="updateProfile()" x-init="init()">
    <form method="post" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data"
        class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="flex space-x-2 items-center sm:space-x-5">
            <div class="overflow-hidden">
                <img id="profile_picture_preview" :src="imageUrl" class="w-16 mb-1 rounded-full object-contain">
                <button type="button" class="text-sm text-gray-500 w-full underline underline-offset-2"
                    @click="removeImage">Retirer</button>
            </div>

            <div class="flex-grow">
                <x-input-label for="profile_picture_input" value="Photo de profil (facultatif)" />
                <input type="file" id="profile_picture_input" name="profile_picture" class="mt-1 block w-full"
                    accept="image/png, image/jpeg" @change="fileChosen">
                <x-input-error class="mt-2" :messages="$errors->get('profile_picture')" />
            </div>
        </div>

        <div>
            <input x-model="imageRemoved" name="remove_image" type="checkbox" class="hidden" aria-hidden="true">
            <x-input-error class="mt-2" :messages="$errors->get('remove_image')" />
        </div>

        <div>
            <x-input-label for="name" value="Nom (visible pour les autres utilisateurs)" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)"
                required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="Adresse mail" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                :value="old('email', $user->email)" required autocomplete="email" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <hr>

        <div class="flex flex-col space-y-6 w-full sm:flex-row sm:space-y-0 sm:space-x-3">
            <div class="sm:w-1/2">
                <x-input-label for="lastname" value="Nom" />
                <x-text-input id="lastname" name="lastname" type="text" class="mt-1 block w-full"
                    :value="old('lastname', $user->lastname)" autocomplete="family-name" />
                <x-input-error class="mt-2" :messages="$errors->get('lastname')" />
            </div>
            <div class="sm:w-1/2">
                <x-input-label for="firstname" value="Prénom" />
                <x-text-input id="firstname" name="firstname" type="text" class="mt-1 block w-full"
                    :value="old('firstname', $user->firstname)" autocomplete="given-name" />
                <x-input-error class="mt-2" :messages="$errors->get('firstname')" />
            </div>
        </div>

        <div>
            <x-input-label for="birthdate" value="Date de naissance" />
            <x-text-input id="birthdate" name="birthdate" type="date" class="mt-1 block w-full"
                :value="old('birthdate', $user->birthdate) !== null ? Carbon\Carbon::parse(old('birthdate', $user->birthdate))->format('Y-m-d') : ''"
                max="{{ now()->format('Y-m-d') }}" autocomplete="bday" />
            <x-input-error class="mt-2" :messages="$errors->get('birthdate')" />
        </div>

        <div>
            <x-input-label for="mobile_phone" value="N° de téléphone portable" />
            <div class="relative">
                <p class="absolute top-[0.55rem] left-2 text-gray-600">+33</p>
                <x-text-input id="mobile_phone" name="mobile_phone" type="tel" class="mt-1 pl-11 block w-full"
                    :value="chunk_split(old('mobile_phone', $user->mobile_phone), 2, ' ')" autocomplete="tel"
                    placeholder="06 01 02 03 04" pattern="0[1-9](?: [0-9]{2}){4}"
                    title="Numéro de téléphone en format français, 10 chiffres de long" />
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('mobile_phone')" />
        </div>

        <div>
            <x-input-label for="home_phone" value="N° de téléphone fixe" />
            <div class="relative">
                <p class="absolute top-[0.55rem] left-2 text-gray-600">+33</p>
                <x-text-input id="home_phone" name="home_phone" type="tel" class="mt-1 pl-11 block w-full"
                    :value="chunk_split(old('home_phone', $user->home_phone), 2, ' ')" autocomplete="tel"
                    placeholder="02 99 98 97 96" pattern="0[1-9](?: [0-9]{2}){4}"
                    title="Numéro de téléphone en format français, 10 chiffres de long" />
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('home_phone')" />
        </div>

        <div>
            <x-input-label for="address" value="Adresse" />
            <x-text-input id="address" name="address" type="text" class="mt-1 block w-full"
                :value="old('address', $user->address)" autocomplete="street-address" />
            <x-input-error class="mt-2" :messages="$errors->get('address')" />
        </div>

        <div>
            <x-input-label for="clothing_size" value="Taille de vétements" />
            <x-select-input id="clothing_size" name="clothing_size" class="mt-1 block w-full"
                :value="old('clothing_size', $user->clothing_size)">
                <option {{ $user->clothing_size ? '' : 'selected' }}>--</option>
                @foreach(['XS','S','M','L','XL','XXL'] as $size)
                <option {{$size==$user->clothing_size ? 'selected' : '' }}>{{ $size }}</option>
                @endforeach
            </x-select-input>
            <x-input-error class="mt-2" :messages="$errors->get('address')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Mettre à jour</x-primary-button>

            @if (session('status') === 'user-updated')
            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                class="text-sm text-gray-600">Enregistré.</p>
            @endif
        </div>
    </form>
</section>

@push('scripts')
<script>
    const MAX_WIDTH = 100;
    const MAX_HEIGHT = 100;
    const MIME_TYPE = "image/jpeg";
    const QUALITY = 0.8;

    function updateProfile() {
        return {
            imageUrl: @js($user -> profile_picture_path ?? Vite:: asset('resources/images/default-pfp.png')),
        imageRemoved: false,
            init() {
            this.imageRemoved = document.getElementById('profile_picture_preview').getAttribute('src') == '';
        },
        removeImage: function () {
            this.imageRemoved = confirm('Supprimer votre image de profil ?');
            if (this.imageRemoved) {
                this.imageUrl = @js(Vite:: asset('resources/images/default-pfp.png'));
                document.getElementById('profile_picture_input').value = '';
            }
        },
        fileChosen(event) {
            this.resizeImage(event);
            this.fileToDataUrl(event, (src) => (this.imageUrl = src));
        },
        fileToDataUrl(event, callback) {
            if (!event.target.files.length) return;

            let file = event.target.files[0],
                reader = new FileReader();

            reader.readAsDataURL(file);
            reader.onload = (e) => callback(e.target.result);
        },
        resizeImage(event) {
            const file = event.target.files[0];
            const blobURL = URL.createObjectURL(file);
            const img = new Image();
            const dataTransfer = new DataTransfer();
            img.src = blobURL;
            img.onerror = function () {
                URL.revokeObjectURL(this.src);
                // Handle the failure properly
                console.log("Cannot load image");
            };
            img.onload = () => {
                URL.revokeObjectURL(this.src);
                const [newWidth, newHeight] = this.calculateSize(img, MAX_WIDTH, MAX_HEIGHT);
                const canvas = document.createElement("canvas");
                canvas.width = newWidth;
                canvas.height = newHeight;
                const ctx = canvas.getContext("2d");
                ctx.drawImage(img, 0, 0, newWidth, newHeight);
                canvas.toBlob(
                    (blob) => {
                        dataTransfer.items.add(new File([blob], file.name, { type: MIME_TYPE }));
                        event.target.files = dataTransfer.files
                        return;
                    },
                    MIME_TYPE,
                    QUALITY
                );
            };
        },
        calculateSize(img, maxWidth, maxHeight) {
            let ratio = Math.min(1, maxWidth / img.naturalWidth, maxHeight / img.naturalHeight);
            return [img.naturalWidth * ratio, img.naturalHeight * ratio];
        }
    };
    }

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

    onload = function () {
        document.getElementById('mobile_phone').oninput = function () {
            this.value = this.value.replace(/\D+/g, '');
            this.value = phone_number_format(this.value);
        }

        document.getElementById('home_phone').oninput = function () {
            this.value = this.value.replace(/\D+/g, '');
            this.value = phone_number_format(this.value);
        }
    }
</script>
@endpush
