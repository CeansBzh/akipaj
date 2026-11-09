{{--
    Les matelots du club, pour retrouver quelqu'un : le matelot connecté en premier, puis les autres du plus grand
    au plus petit nombre de sorties. La recherche filtre la liste pendant la frappe, sans tenir compte des accents,
    des majuscules ni des tirets, et reste en haut de l'écran pendant qu'on fait défiler les cartes.
--}}

<x-member-layout>
    <div x-data="memberSearch(@js($members->pluck('name')))" class="bg-primary-50 pb-24">
        <x-signal-flags class="pointer-events-none" />

        <header class="mx-auto max-w-2xl px-4 text-center sm:px-6">
            <h1 class="text-4xl font-bold text-gray-900 sm:text-5xl">Les matelots</h1>
        </header>

        <div class="mx-auto mt-8 max-w-2xl px-4 sm:px-6">
            <label for="member-search" class="block font-semibold text-gray-900">Chercher un matelot</label>
        </div>
        {{-- Seul le champ reste en haut de l'écran pendant qu'on fait défiler les cartes --}}
        <div role="search" class="sticky top-0 z-20 bg-primary-50/90 py-2 backdrop-blur-md">
            <div class="mx-auto max-w-2xl px-4 sm:px-6">
                <div class="relative">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round"
                        class="pointer-events-none absolute top-1/2 left-5 size-5 -translate-y-1/2 text-gray-600">
                        <circle cx="11" cy="11" r="7" />
                        <path d="m20 20-4-4" />
                    </svg>
                    {{-- La liste se filtre pendant la frappe : la touche Entrée ferme seulement le clavier du téléphone --}}
                    <input type="search" id="member-search" x-ref="search" x-model="query"
                        x-on:keydown.enter.prevent="$el.blur()" autocomplete="off" spellcheck="false"
                        enterkeyhint="search" placeholder="Pseudonyme" aria-describedby="member-search-result"
                        class="block min-h-14 w-full rounded-full border-0 bg-white py-3 pr-32 pl-13 text-lg text-gray-900 shadow-sm ring-1 ring-gray-300 ring-inset placeholder:text-gray-500 focus:ring-2 focus:ring-primary-700 [&::-webkit-search-cancel-button]:appearance-none">
                    <button type="button" x-cloak x-show="query !== ''" x-on:click="clear()"
                        class="absolute top-1/2 right-1.5 inline-flex min-h-11 -translate-y-1/2 items-center gap-1.5 rounded-full px-4 font-semibold text-primary-800 transition-colors hover:bg-primary-100 focus-visible:outline-2 focus-visible:outline-primary-700">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" class="size-4 shrink-0">
                            <path d="M6 6l12 12M18 6 6 18" />
                        </svg>
                        Effacer
                    </button>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <p id="member-search-result" aria-live="polite" class="mx-auto min-h-8 max-w-2xl pt-1 text-gray-700"
                x-text="summary"></p>

            <ul class="mt-2 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 md:grid-cols-4 lg:grid-cols-5">
                @foreach ($members as $member)
                    @php($isCurrentUser = $member->is(auth()->user()))
                    @php($tripCount = (int) $member->trips_count)
                    <li x-show="visible[{{ $loop->index }}]" class="min-w-0">
                        <a href="{{ route('profile.show', $member->name) }}" @class([
                            'group flex h-full flex-col items-center rounded-3xl bg-white px-2 pt-6 pb-5 text-center shadow-sm transition hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700 motion-safe:hover:-translate-y-0.5',
                            'ring-2 ring-primary-600' => $isCurrentUser,
                            'ring-1 ring-gray-900/5' => !$isCurrentUser,
                        ])>
                            <span class="relative">
                                <x-user-avatar :user="$member" class="size-20 sm:size-24" />
                                @if ($isCurrentUser)
                                    <span class="absolute -bottom-2 left-1/2 -translate-x-1/2 rounded-full bg-primary-700 px-2.5 py-0.5 text-sm font-bold text-white ring-2 ring-white">
                                        Vous
                                    </span>
                                @endif
                            </span>
                            <span class="mt-4 text-lg leading-snug font-bold text-balance break-words text-gray-900 group-hover:underline">
                                {{ $member->name }}
                            </span>
                            <span class="mt-auto pt-3">
                                <span @class([
                                    'inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-sm font-semibold',
                                    'bg-primary-50 text-primary-800' => $tripCount > 0,
                                    'bg-gray-100 text-gray-700' => $tripCount === 0,
                                ])>
                                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                                        class="size-4 shrink-0">
                                        <path d="M11 3 4.5 15.5H11V3ZM14 6.5l5.5 9H14v-9ZM3 18.5h18l-2.5 2.5h-13L3 18.5Z" />
                                    </svg>
                                    @if ($tripCount === 0)
                                        Aucune sortie
                                    @else
                                        {{ $tripCount }} {{ $tripCount > 1 ? 'sorties' : 'sortie' }}
                                    @endif
                                </span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>

            <div x-cloak x-show="count === 0" class="mx-auto mt-6 max-w-md rounded-3xl bg-white px-6 py-10 text-center shadow-sm">
                <p class="text-lg font-bold text-gray-900">Aucun matelot ne porte ce nom.</p>
                <p class="mt-2 text-gray-700">Vérifiez l'orthographe, ou tapez seulement le début du pseudo.</p>
                <button type="button" x-on:click="clear()"
                    class="mt-6 inline-flex min-h-11 items-center rounded-full bg-primary-700 px-5 py-2 font-semibold text-white transition-colors hover:bg-primary-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700">
                    Effacer la recherche
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                /**
                 * Filtre la liste des matelots pendant la frappe : chaque mot tapé doit se trouver dans le nom.
                 *
                 * @param {string[]} names Noms des matelots, dans l'ordre de la liste
                 */
                Alpine.data('memberSearch', (names) => {
                    // Sans accents, en minuscules, et les tirets ou apostrophes comme des espaces : « jean-rene » trouve « Jean René »
                    const simplify = (text) =>
                        text
                            .normalize('NFD')
                            .replace(/\p{Diacritic}/gu, '')
                            .toLowerCase()
                            .replace(/[^\p{L}\p{N}]+/gu, ' ')
                            .trim();
                    const searchableNames = names.map(simplify);

                    return {
                        query: '',
                        visible: searchableNames.map(() => true),

                        init() {
                            this.$watch('query', () => {
                                const words = simplify(this.query).split(' ').filter(Boolean);
                                this.visible = searchableNames.map((name) => words.every((word) => name.includes(word)));
                            });
                        },

                        get count() {
                            return this.visible.filter(Boolean).length;
                        },

                        get summary() {
                            if (this.query.trim() === '') {
                                return '';
                            }
                            if (this.count === 0) {
                                return 'Aucun matelot trouvé';
                            }

                            return this.count === 1 ? '1 matelot trouvé' : `${this.count} matelots trouvés`;
                        },

                        clear() {
                            this.query = '';
                            this.$refs.search.focus();
                        },
                    };
                });
            });
        </script>
    @endpush
</x-member-layout>
