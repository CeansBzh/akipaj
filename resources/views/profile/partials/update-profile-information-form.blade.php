<section x-data="updateProfile()" x-init="init()">
    <header>
        <h2 class="text-lg font-medium text-gray-900">Informations du compte</h2>
        <p class="mt-1 text-sm text-gray-600">Nous utiliserons votre adresse mail pour tous les messages relatifs à
            l'activité du site.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
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

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div>
                <p class="text-sm mt-2 text-gray-800">
                    Votre adresse mail n'a pas été confirmée.

                    <button form="send-verification"
                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500">
                        Cliquer ici pour recevoir à nouveau le lien de confirmation.
                    </button>
                </p>

                @if (session('status') === 'verification-link-sent')
                <p class="mt-2 font-medium text-sm text-green-600">
                    Un nouveau lien viens de vous être envoyé par mail.
                </p>
                @endif
            </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Modifier</x-primary-button>

            @if (session('status') === 'profile-updated')
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
</script>
@endpush
