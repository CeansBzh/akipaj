<section class="mx-auto max-w-2xl">
    <header>
        <h2 class="text-lg font-medium text-gray-900">Modifier les détails</h2>
        <p class="mt-1 text-sm text-gray-600">Partagez aux autres membres une anecdote croustillante sur cette photo !
        </p>
    </header>

    <form method="post" action="{{ route('photos.update', $photo) }}" class="mt-6 space-y-6">
        @method('patch')
        @csrf

        <div>
            <x-input-label for="title_input" value="Titre" />
            <x-text-input id="title_input" name="title" type="text" class="mt-1 block w-full" :value="$photo->title"
                maxlength="255" required autofocus />
            <x-input-error class="mt-2" :messages="$errors->updatePhoto->get('title')" />
        </div>

        <hr>

        <div>
            <x-input-label for="legend_input" value="Légende (facultatif)" />
            <x-textarea-input id="legend_input" name="legend" class="mt-1 block w-full" maxlength="2048"
                rows="5">
                {{ $photo->legend }}
            </x-textarea-input>
            <x-input-error class="mt-2" :messages="$errors->updatePhoto->get('legend')" />
        </div>

        <div>
            <x-input-label for="taken_at_input" value="Date (facultatif)" />
            <x-text-input id="taken_at_input" name="taken_at" type="date" class="mt-1 block w-full"
                :value="$photo->taken_at" />
            <x-input-error class="mt-2" :messages="$errors->updatePhoto->get('taken_at')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Mettre à jour</x-primary-button>
        </div>
    </form>
</section>
