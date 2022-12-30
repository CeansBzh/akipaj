<div x-data="{ boats: @entangle('boats') }">
    <div class="flex items-center gap-4">
        <x-secondary-button type="button" class="mx-auto sm:mx-0"
            x-on:click.prevent="$dispatch('open-modal', 'add-boat')">Associer un bateau
        </x-secondary-button>
    </div>

    <input id="boats" type="hidden" name="boats" wire:model="boatsInputData" />

    <ul class="space-y-1">
        <template x-for="(boat, index) in boats">
            <div class="p-2 rounded border bg-sky-100 flex">
                <p x-text="boat.name + ', ' + boat.renter + ', ' + boat.year" class="text-gray-700"></p>
                <button type="button" class="ml-auto" x-on:click.prevent="boats.splice(index, 1)">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="h-6 text-red-500">
                        <path d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </template>
    </ul>

    <x-modal name="add-boat" focusable>
        <div class="p-10" x-on:close-modal.window="$dispatch('close')">
            <h3>Ajouter un bateau</h3>
            <div class="mt-6 space-y-6">
                <div>
                    <x-input-label for="name_input" value="Nom" />
                    <x-text-input id="name_input" name="name" type="text" wire:model="name" class="mt-1 block w-full"
                        :value="old('name')" maxlength="255" />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <div>
                    <x-input-label for="type_input" value="Type" />
                    <x-text-input id="type_input" name="type" type="text" wire:model="type" class="mt-1 block w-full"
                        :value="old('type')" maxlength="255" />
                    <x-input-error class="mt-2" :messages="$errors->get('type')" />
                </div>

                <div>
                    <x-input-label for="year_input" value="Année" />
                    <x-text-input id="year_input" name="year" type="number" wire:model="year" class="mt-1 block w-full"
                        :value="old('year')" min="1900" max="9999" />
                    <x-input-error class="mt-2" :messages="$errors->get('year')" />
                </div>

                <div>
                    <x-input-label for="builder_input" value="Constructeur" />
                    <x-text-input id="builder_input" name="builder" type="text" wire:model="builder"
                        class="mt-1 block w-full" :value="old('builder')" maxlength="255" />
                    <x-input-error class="mt-2" :messages="$errors->get('builder')" />
                </div>

                <div>
                    <x-input-label for="renter_input" value="Loueur" />
                    <x-text-input id="renter_input" name="renter" type="text" wire:model="renter"
                        class="mt-1 block w-full" :value="old('renter')" maxlength="255" />
                    <x-input-error class="mt-2" :messages="$errors->get('renter')" />
                </div>

                <div>
                    <x-input-label for="navigation_area_input" value="Zone de navigation" />
                    <x-text-input id="navigation_area_input" name="navigation_area" type="text"
                        wire:model="navigation_area" class="mt-1 block w-full" :value="old('navigation_area')"
                        maxlength="255" />
                    <x-input-error class="mt-2" :messages="$errors->get('navigation_area')" />
                </div>

                <div>
                    <x-input-label for="city_input" value="Ville" />
                    <x-text-input id="city_input" name="city" type="text" wire:model="city" class="mt-1 block w-full"
                        :value="old('city')" maxlength="255" />
                    <x-input-error class="mt-2" :messages="$errors->get('city')" />
                </div>

                <div>
                    <x-input-label for="crew_input" value="Équipage (nombre)" />
                    <x-text-input id="crew_input" name="crew" type="number" wire:model="crew" class="mt-1 block w-full"
                        :value="old('crew')" maxlength="255" />
                    <x-input-error class="mt-2" :messages="$errors->get('crew')" />
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button type="button" wire:click="addBoat">Créer le bateau</x-primary-button>
                </div>

            </div>
        </div>
    </x-modal>

</div>
