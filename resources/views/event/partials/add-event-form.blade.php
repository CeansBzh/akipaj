<section class="max-w-2xl mx-auto">
    <header>
        <h2 class="text-lg font-medium text-gray-900">Ajouter un événement</h2>
        <p class="mt-1 text-sm text-gray-600">L'événement sera visible à tous les membres.</p>
    </header>

    <form method="post" action="{{ route('events.store') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf

        <div>
            <x-input-label for="name_input" value="Nom" />
            <x-text-input id="name_input" name="name" type="text" class="mt-1 block w-full" :value="old('name')"
                placeholder="WK Boat Açores" maxlength="50" required autofocus />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="flex space-x-5 w-full">
            <div>
                <x-input-label for="start_time_input" value="Date de début" />
                <x-text-input id="start_time_input" name="start_time" type="date" class="mt-1 block w-full"
                    :value="old('start_time', date('Y-m-d'))" :min="date('Y-m-d')" required />
                <x-input-error class="mt-2" :messages="$errors->get('start_time')" />
            </div>

            <div>
                <x-input-label for="end_time_input" value="Date de fin" />
                <x-text-input id="end_time_input" name="end_time" type="date" class="mt-1 block w-full"
                    :value="old('end_time', now()->addDays(10)->format('Y-m-d'))" :min="date('Y-m-d')" required />
                <x-input-error class="mt-2" :messages="$errors->get('end_time')" />
            </div>
        </div>

        <div>
            <x-input-label for="description_input" value="Description" />
            <x-textarea-input id="description_input" name="description" class="mt-1 block w-full"
                placeholder="Description de l'événement" maxlength="140" required>
                {{ old('description')}}
            </x-textarea-input>
            <x-input-error class="mt-2" :messages="$errors->get('description')" />
        </div>

        <div>
            <x-input-label for="location_input" value="Adresse (facultatif)" />
            <x-text-input id="location_input" name="location" type="text" class="mt-1 block w-full"
                :value="old('location')" placeholder="Port de plaisance de Horta, Portugal" maxlength="255" />
            <x-input-error class="mt-2" :messages="$errors->get('location')" />
        </div>

        <div>
            <x-input-label for="image_input" value="Image de couverture (facultatif)" />
            <input type="file" id="image_input" name="image" class="mt-1 block w-full" accept="image/png, image/jpeg">
            <x-input-error class="mt-2" :messages="$errors->get('image')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Ajouter</x-primary-button>
        </div>
    </form>
</section>