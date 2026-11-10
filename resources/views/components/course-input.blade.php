@props([
    'points' => [],
    'errorBag' => 'default',
    'startInput' => 'start_date_input',
    'endInput' => 'end_date_input',
])

{{--
    Parcours d'une sortie dans son formulaire : une carte pour placer les points, une recherche de lieu par son nom,
    puis la liste des points, rangés par jour, à nommer et à ordonner.
    Les jours proposés suivent les dates de début et de fin choisies dans les champs startInput et endInput.
    Tant que la carte n'est pas chargée, aucun champ du parcours n'est envoyé et le parcours enregistré ne change pas.
--}}

@php
    $messages = collect($errors->getBag($errorBag)->getMessages())
        ->filter(fn(array $messages, string $key): bool => $key === 'course' || Str::startsWith($key, 'course.'))
        ->flatten()
        ->unique()
        ->values()
        ->all();
    $buttonClass = 'min-h-11 gap-1.5';
@endphp

<div data-course-component x-ignore x-data="courseInput(@js(['points' => $points ?? [], 'startInput' => $startInput, 'endInput' => $endInput]))" {{ $attributes }}>
    <p class="text-gray-600" x-bind:hidden="true">Chargement de la carte du parcours…</p>

    <div hidden x-bind:hidden="false" class="flex flex-col gap-5">
        <div>
            <x-input-label for="course_search_input" value="Chercher un lieu par son nom" />
            <div class="mt-1 flex gap-2">
                <x-text-input id="course_search_input" type="search" class="block w-full min-w-0" x-model="query"
                    x-on:keydown.enter.prevent="search()" placeholder="Port-Tudy, Belle-Île…" autocomplete="off" />
                <x-secondary-button class="{{ $buttonClass }} shrink-0" x-on:click="search()"
                    x-bind:disabled="searching">
                    Chercher
                </x-secondary-button>
            </div>
            <p class="mt-2 text-gray-700" x-show="searchMessage" x-text="searchMessage" aria-live="polite"></p>
            <ul class="mt-2 flex flex-col gap-2" x-show="results.length > 0">
                <template x-for="result in results" :key="result.id">
                    <li>
                        <button type="button" x-on:click="addResult(result)"
                            class="focus-visible:outline-primary-700 flex min-h-11 w-full items-center justify-between gap-3 rounded-md border border-gray-300 bg-white px-3 py-2 text-left hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-offset-2">
                            <span class="min-w-0 wrap-break-word text-gray-900" x-text="result.label"></span>
                            <span class="text-primary-800 shrink-0 font-semibold">Ajouter</span>
                        </button>
                    </li>
                </template>
            </ul>
        </div>

        <div>
            <p class="text-gray-600" x-show="relocatingKey === null">
                Touchez la carte pour ajouter un point. Vous pouvez aussi faire glisser un point pour le déplacer.
            </p>
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1" x-show="relocatingKey !== null" aria-live="polite">
                <p class="text-primary-800 font-semibold">
                    Touchez la carte à l'endroit où placer « <span x-text="relocatingName()"></span> ».
                </p>
                <x-secondary-button class="{{ $buttonClass }}"
                    x-on:click="relocatingKey = null">Annuler</x-secondary-button>
            </div>
            <div x-ref="map" class="course-map bg-primary-50 isolate mt-2 h-80 rounded-2xl sm:h-96"
                x-bind:class="relocatingKey !== null && 'cursor-crosshair'"></div>
        </div>

        {{-- Un parcours vide est envoyé quand même, pour effacer les points enregistrés --}}
        <template x-if="points.length === 0">
            <input type="hidden" name="course" value="">
        </template>
        <p class="text-gray-600" x-show="points.length === 0">Aucun point pour l'instant.</p>

        <ol class="flex flex-col gap-3">
            <template x-for="(point, index) in points" :key="point.key">
                <li>
                    <p class="mb-2 mt-2 font-bold text-gray-900" x-show="startsDay(index)" x-text="dayLabel(point.day)">
                    </p>
                    <div class="rounded-2xl bg-gray-50 p-4 ring-1 ring-inset ring-gray-200">
                        <div class="flex items-start gap-3">
                            <span
                                class="bg-primary-700 mt-7 flex size-8 shrink-0 items-center justify-center rounded-full text-sm font-bold text-white"
                                x-text="index + 1" aria-hidden="true"></span>
                            <div class="grid min-w-0 grow gap-3 sm:grid-cols-[minmax(0,1fr)_auto]">
                                <div class="min-w-0">
                                    <x-input-label x-bind:for="`course_point_${point.key}_name`">
                                        Nom du point <span x-text="index + 1"></span>
                                    </x-input-label>
                                    <x-text-input type="text" class="mt-1 block min-h-11 w-full" required
                                        maxlength="100" x-bind:id="`course_point_${point.key}_name`"
                                        x-bind:name="`course[${index}][name]`" x-model="point.name" />
                                </div>
                                <div>
                                    <x-input-label x-bind:for="`course_point_${point.key}_day`" value="Jour" />
                                    <x-select-input class="mt-1 block min-h-11 w-full"
                                        x-bind:id="`course_point_${point.key}_day`"
                                        x-bind:name="`course[${index}][day]`"
                                        x-on:change="setDay(point.key, $event.target.value)">
                                        <template x-for="day in days" :key="day">
                                            <option x-bind:value="day" x-bind:selected="day === point.day"
                                                x-text="dayLabel(day)"></option>
                                        </template>
                                    </x-select-input>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2 sm:pl-11">
                            <x-secondary-button class="{{ $buttonClass }}" x-on:click="moveUp(index)"
                                x-bind:disabled="index === 0">
                                Monter
                            </x-secondary-button>
                            <x-secondary-button class="{{ $buttonClass }}" x-on:click="moveDown(index)"
                                x-bind:disabled="index === points.length - 1">
                                Descendre
                            </x-secondary-button>
                            <x-secondary-button class="{{ $buttonClass }}"
                                x-on:click="relocatingKey = point.key; $refs.map.scrollIntoView({ block: 'nearest' })">
                                Replacer
                            </x-secondary-button>
                            <x-secondary-button class="{{ $buttonClass }}" x-on:click="remove(index)">
                                Retirer
                            </x-secondary-button>
                        </div>

                        <input type="hidden" x-bind:name="`course[${index}][latitude]`"
                            x-bind:value="point.latitude">
                        <input type="hidden" x-bind:name="`course[${index}][longitude]`"
                            x-bind:value="point.longitude">
                    </div>
                </li>
            </template>
        </ol>
    </div>

    <x-input-error class="mt-2" :messages="$messages" />
</div>

@once
    @push('scripts')
        @vite('resources/js/course-map.js')
    @endpush
@endonce
