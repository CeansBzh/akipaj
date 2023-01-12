<div>
    <div class="mb-5 flex justify-between items-center">
        <h3>Associer des bateaux</h3>
        <x-secondary-button wire:click.prevent="addBoat">Nouveau bateau</x-secondary-button>
    </div>

    @if (!empty($boats))
        <div class="relative overflow-x-auto shadow-md border rounded">
            <table class="w-full text-sm text-left text-gray-700">
                <tr class="border-b border-gray-200">
                    <th scope="row" class="text-xs text-gray-800 uppercase px-6 py-3 bg-gray-50">Nom</th>
                    @foreach ($boats as $index => $boat)
                        <th scope="col">
                            <x-input-label for="name_input_{{ $index }}" value="Nom" class="sr-only" />
                            <input id="name_input_{{ $index }}" name="boats[{{ $index }}][name]"
                                type="text" wire:model="boats.{{ $index }}.name"
                                class="w-full font-medium border-gray-300 focus:border-sky-500 focus:ring-sky-500"
                                maxlength="255" required>
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                            <input type="hidden" name="boats[{{ $index }}][id]" wire:model="boats.{{ $index }}.id" aria-hidden="true">
                        </th>
                    @endforeach
                </tr>
                <tr class="border-b border-gray-200">
                    <th scope="row" class="text-xs text-gray-800 uppercase px-6 py-3 bg-gray-50">Type</th>
                    @foreach ($boats as $index => $boat)
                        <td>
                            <x-input-label for="type_input_{{ $index }}" value="Type" class="sr-only" />
                            <input id="type_input_{{ $index }}" name="boats[{{ $index }}][type]"
                                type="text" wire:model="boats.{{ $index }}.type"
                                class="w-full border-gray-300 focus:border-sky-500 focus:ring-sky-500" maxlength="255">
                            <x-input-error class="mt-2" :messages="$errors->get('type')" />
                        </td>
                    @endforeach
                </tr>
                <tr class="border-b border-gray-200">
                    <th scope="row" class="text-xs text-gray-800 uppercase px-6 py-3 bg-gray-50">Année</th>
                    @foreach ($boats as $index => $boat)
                        <td>
                            <x-input-label for="year_input_{{ $index }}" value="Année" class="sr-only" />
                            <input id="year_input_{{ $index }}" name="boats[{{ $index }}][year]"
                                type="number" wire:model="boats.{{ $index }}.year"
                                class="w-full border-gray-300 focus:border-sky-500 focus:ring-sky-500" min="1"
                                max="9999">
                            <x-input-error class="mt-2" :messages="$errors->get('year')" />
                        </td>
                    @endforeach
                </tr>
                <tr class="border-b border-gray-200">
                    <th scope="row" class="text-xs text-gray-800 uppercase px-6 py-3 bg-gray-50">Constructeur</th>
                    @foreach ($boats as $index => $boat)
                        <td>
                            <x-input-label for="builder_input_{{ $index }}" value="Constructeur"
                                class="sr-only" />
                            <input id="builder_input_{{ $index }}" name="boats[{{ $index }}][builder]"
                                type="text" wire:model="boats.{{ $index }}.builder"
                                class="w-full border-gray-300 focus:border-sky-500 focus:ring-sky-500" maxlength="255">
                            <x-input-error class="mt-2" :messages="$errors->get('builder')" />
                        </td>
                    @endforeach
                </tr>
                <tr class="border-b border-gray-200">
                    <th scope="row" class="text-xs text-gray-800 uppercase px-6 py-3 bg-gray-50">Loueur</th>
                    @foreach ($boats as $index => $boat)
                        <td>
                            <x-input-label for="renter_input_{{ $index }}" value="Loueur" class="sr-only" />
                            <input id="renter_input_{{ $index }}" name="boats[{{ $index }}][renter]"
                                type="text" wire:model="boats.{{ $index }}.renter"
                                class="w-full border-gray-300 focus:border-sky-500 focus:ring-sky-500" maxlength="255">
                            <x-input-error class="mt-2" :messages="$errors->get('renter')" />
                        </td>
                    @endforeach
                </tr>
                <tr class="border-b border-gray-200">
                    <th scope="row" class="text-xs text-gray-800 uppercase px-6 py-3 bg-gray-50">Port</th>
                    @foreach ($boats as $index => $boat)
                        <td>
                            <x-input-label for="city_input_{{ $index }}" value="Loueur" class="sr-only" />
                            <input id="city_input_{{ $index }}" name="boats[{{ $index }}][city]"
                                type="text" wire:model="boats.{{ $index }}.city"
                                class="w-full border-gray-300 focus:border-sky-500 focus:ring-sky-500" maxlength="255">
                            <x-input-error class="mt-2" :messages="$errors->get('city')" />
                        </td>
                    @endforeach
                </tr>
                <tr class="border-b border-gray-200">
                    <th scope="row" class="text-xs text-gray-800 uppercase px-6 py-3 bg-gray-50">Équipage</th>
                    @foreach ($boats as $index => $boat)
                        <td scope="row">
                            <x-input-label for="crew_input_{{ $index }}" value="Nombre d'équipiers"
                                class="sr-only" />
                            <input id="crew_input_{{ $index }}" name="boats[{{ $index }}][crew]"
                                type="number" wire:model="boats.{{ $index }}.crew"
                                class="w-full border-gray-300 focus:border-sky-500 focus:ring-sky-500" min="0"
                                max="9999">
                            <x-input-error class="mt-2" :messages="$errors->get('crew')" />
                        </td>
                    @endforeach
                </tr>
                <tr class="border-b border-gray-200">
                    <th scope="row"></th>
                    @foreach ($boats as $index => $boat)
                        <td scope="row" class="p-1 text-center">
                            <button type="button" wire:click.prevent="deleteBoat({{ $index }})">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="h-6 text-red-500">
                                    <path d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </td>
                    @endforeach
                </tr>
            </table>
        </div>
    @endif
</div>
