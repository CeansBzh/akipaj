@php
    $photo = $this->photo;
    // Boutons sur le fond sombre de la visionneuse
    $focusRing = 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white';
    $primaryButton =
        'inline-flex min-h-11 items-center gap-2 rounded-full bg-white px-5 py-2 font-semibold text-gray-950 transition-colors hover:bg-gray-200 disabled:cursor-wait disabled:opacity-60 ' .
        $focusRing;
    $secondaryButton =
        'inline-flex min-h-11 items-center gap-2 rounded-full px-5 py-2 font-semibold text-white ring-1 ring-white/30 ring-inset transition-colors hover:bg-white/10 ' .
        $focusRing;
    $quietButton =
        'inline-flex min-h-11 items-center gap-1.5 rounded-full px-3 font-semibold text-gray-300 transition-colors hover:bg-white/10 hover:text-white ' .
        $focusRing;
    $field =
        'block w-full resize-none rounded-3xl border-0 bg-white/10 px-4 py-3 text-white ring-1 ring-white/25 ring-inset field-sizing-content placeholder:text-gray-400 focus:ring-2 focus:ring-white';
    $factIcon = 'mt-0.5 size-5 shrink-0 text-gray-400';
    $factLabel = 'text-sm font-semibold text-gray-400';
@endphp

{{--
    Panneaux « Détails » et « Commentaires » de la visionneuse, sombres comme elle, posés à sa droite.
    resources/js/gallery.js les ouvre et les ferme par des événements : photo-drawer-open (photo et panneau),
    photo-drawer-photo (la visionneuse change de photo) et photo-drawer-close. Le bouton de fermeture demande
    photo-drawer-dismiss, pour que la visionneuse referme aussi l'entrée qu'elle a ajoutée à l'historique.
--}}
<div x-data="{
    open: false,
    panel: 'details',
    photoId: null,
    posting: false,
    show(photoId) {
        this.photoId = Number(photoId);
        if ($wire.photoId !== this.photoId) {
            $wire.show(this.photoId);
        }
    },
    async postComment() {
        if (this.posting) return;
        this.posting = true;
        try {
            await $wire.postComment();
        } finally {
            this.posting = false;
        }
        // Le nouveau commentaire, en bas de la liste, vient à l'écran
        this.$nextTick(() => this.$refs.body.scrollTo({ top: this.$refs.body.scrollHeight }));
    },
}"
    x-on:photo-drawer-open.window="
    open = true;
    panel = $event.detail.panel;
    show($event.detail.photoId);
    $nextTick(() => $refs.drawer.focus());
"
    x-on:photo-drawer-photo.window="open && show($event.detail.photoId)" x-on:photo-drawer-close.window="open = false">
    {{-- Dans le body, au-dessus de la visionneuse que PhotoSwipe y ajoute --}}
    @teleport('body')
        <section id="photo-drawer" x-ref="drawer" data-photo-drawer x-show="open" x-cloak role="dialog" tabindex="-1"
            aria-labelledby="photo-drawer-title" x-bind:aria-busy="$wire.photoId !== photoId"
            x-transition:enter="transition-transform duration-200 ease-out motion-reduce:transition-none"
            x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition-transform duration-150 ease-in motion-reduce:transition-none"
            x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
            class="z-100010 scheme-dark md:w-104 fixed inset-y-0 right-0 flex w-full flex-col bg-gray-950/90 font-sans text-gray-100 shadow-2xl ring-1 ring-white/10 backdrop-blur-xl focus:outline-none">

            <header
                class="flex items-center justify-between gap-3 border-b border-white/10 py-3 pl-5 pr-3 pt-[max(0.75rem,env(safe-area-inset-top))]">
                <h2 id="photo-drawer-title" class="text-xl font-bold text-white">
                    <span x-show="panel === 'details'">Détails</span>
                    <span x-show="panel === 'comments'" x-cloak>Commentaires</span>
                    <span x-show="panel === 'share'" x-cloak>Partager</span>
                </h2>
                <button type="button" x-on:click="$dispatch('photo-drawer-dismiss')" aria-label="Fermer le panneau"
                    title="Fermer le panneau"
                    class="{{ $focusRing }} inline-flex size-11 shrink-0 items-center justify-center rounded-full bg-white/10 text-white transition-colors hover:bg-white/20">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" class="size-5">
                        <path d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>
            </header>

            <div x-ref="body" class="flex-1 overflow-y-auto overscroll-contain px-5 py-5">
                <p x-show="$wire.photoId !== photoId" class="flex items-center gap-3 pb-5 text-gray-300">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none"
                        class="size-5 shrink-0 motion-safe:animate-spin">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity="0.25"
                            stroke-width="2.5" />
                        <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
                    </svg>
                    Chargement…
                </p>

                @if ($photo)
                    <div x-bind:class="{ 'opacity-40': $wire.photoId !== photoId }" class="transition-opacity">

                        {{-- Détails : le matelot et la légende, puis la prise de vue, le lieu, l'album et la taille --}}
                        <div x-show="panel === 'details'" class="flex flex-col gap-6">
                            <div class="flex items-center gap-3">
                                <x-user-avatar :user="$photo->user" class="size-12" />
                                <p class="min-w-0">
                                    <a href="{{ route('profile.show', $photo->user->name) }}"
                                        class="{{ $focusRing }} wrap-break-word font-bold text-white underline decoration-white/40 underline-offset-4 hover:decoration-white">{{ $photo->user->name }}</a>
                                    <span class="block text-gray-300">
                                        Envoyée le {{ $photo->created_at->isoFormat('dddd D MMMM YYYY') }}
                                    </span>
                                </p>
                            </div>

                            <section aria-labelledby="photo-drawer-legend">
                                <p id="photo-drawer-legend" class="{{ $factLabel }}">Légende</p>
                                @if ($isEditingLegend)
                                    <form wire:submit="saveLegend" class="mt-2">
                                        <label for="photo-drawer-legend-input" class="sr-only">Légende de la photo</label>
                                        <textarea id="photo-drawer-legend-input" wire:model="legend" rows="3" maxlength="2048" x-init="$el.focus()"
                                            class="{{ $field }} min-h-24"></textarea>
                                        @error('legend')
                                            <p class="mt-2 text-red-300">{{ $message }}</p>
                                        @enderror
                                        <div class="mt-3 flex flex-wrap gap-3">
                                            <button type="submit" wire:loading.attr="disabled" wire:target="saveLegend"
                                                class="{{ $primaryButton }}">
                                                Enregistrer la légende
                                            </button>
                                            <button type="button" wire:click="cancelLegend"
                                                class="{{ $secondaryButton }}">Annuler</button>
                                        </div>
                                    </form>
                                @else
                                    @if ($photo->legend)
                                        <p
                                            class="wrap-break-word mt-1 max-w-prose whitespace-pre-line leading-relaxed text-gray-100">
                                            {{ $photo->legend }}</p>
                                    @else
                                        <p class="mt-1 text-gray-300">Pas encore de légende.</p>
                                    @endif
                                    @can('update', $photo)
                                        <button type="button" id="photo-drawer-legend-edit" wire:click="editLegend"
                                            class="{{ $quietButton }} -ml-3 mt-1">
                                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                                                class="size-5 shrink-0">
                                                <path d="M4 20h4L19 9a2.8 2.8 0 0 0-4-4L4 16v4Z" />
                                                <path d="m13.5 6.5 4 4" />
                                            </svg>
                                            {{ $photo->legend ? 'Modifier la légende' : 'Ajouter une légende' }}
                                        </button>
                                    @endcan
                                @endif
                            </section>

                            <dl class="flex flex-col gap-5 border-t border-white/10 pt-6">
                                @if ($photo->taken_at)
                                    <div class="flex gap-3">
                                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                                            class="{{ $factIcon }}">
                                            <path
                                                d="M4 8.5A1.5 1.5 0 0 1 5.5 7h2l1.5-2h6l1.5 2h2A1.5 1.5 0 0 1 20 8.5v9a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 4 17.5v-9Z" />
                                            <circle cx="12" cy="13" r="3.5" />
                                        </svg>
                                        <div>
                                            <dt class="{{ $factLabel }}">Prise le</dt>
                                            <dd class="text-gray-100">
                                                {{ $photo->taken_at->isoFormat($photo->taken_at->format('H:i') === '00:00' ? 'dddd D MMMM YYYY' : 'dddd D MMMM YYYY [à] H [h] mm') }}
                                            </dd>
                                        </div>
                                    </div>
                                @endif

                                @if ($photo->latitude !== null && $photo->longitude !== null)
                                    <div class="flex gap-3">
                                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                                            class="{{ $factIcon }}">
                                            <path d="M12 21s7-6.2 7-11.5a7 7 0 0 0-14 0C5 14.8 12 21 12 21Z" />
                                            <circle cx="12" cy="9.5" r="2.5" />
                                        </svg>
                                        <div class="min-w-0 flex-1">
                                            <dt class="{{ $factLabel }}">Lieu</dt>
                                            <dd class="mt-2">
                                                <x-location-map :latitude="$photo->latitude" :longitude="$photo->longitude"
                                                    wire:key="photo-drawer-map-{{ $photo->id }}" />
                                            </dd>
                                        </div>
                                    </div>
                                @endif

                                @if ($photo->album)
                                    <div class="flex gap-3">
                                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                                            class="{{ $factIcon }}">
                                            <rect x="3.5" y="6.5" width="14" height="13" rx="2" />
                                            <path d="M7 4h11.5A2 2 0 0 1 20.5 6v11M3.5 15.5l4-4 3.5 3.5 2-2 4.5 4.5" />
                                        </svg>
                                        <div class="min-w-0">
                                            <dt class="{{ $factLabel }}">Album</dt>
                                            <dd>
                                                <a href="{{ route('photos.index', ['albums' => [$photo->album->id]]) }}"
                                                    class="{{ $focusRing }} wrap-break-word font-semibold text-white underline decoration-white/40 underline-offset-4 hover:decoration-white">{{ $photo->album->title }}</a>
                                            </dd>
                                        </div>
                                    </div>
                                @endif

                                @if ($photo->width && $photo->height)
                                    @php($fileSize = $photo->fileSize())
                                    <div class="flex gap-3">
                                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                                            class="{{ $factIcon }}">
                                            <path d="M4 9V4h5M20 15v5h-5M15 4h5v5M9 20H4v-5" />
                                        </svg>
                                        <div>
                                            <dt class="{{ $factLabel }}">Taille</dt>
                                            <dd class="tabular-nums text-gray-100">
                                                {{ number_format($photo->width, 0, ',', "\u{202F}") . ' × ' . number_format($photo->height, 0, ',', "\u{202F}") . ' pixels' }}
                                                @if ($fileSize)
                                                    <span class="text-gray-300">·
                                                        {{ $fileSize >= 1048576 ? number_format($fileSize / 1048576, 1, ',', "\u{202F}") . ' Mo' : max(1, round($fileSize / 1024)) . ' ko' }}</span>
                                                @endif
                                            </dd>
                                        </div>
                                    </div>
                                @endif
                            </dl>
                        </div>

                        {{-- Commentaires, du plus ancien au plus récent --}}
                        <div x-show="panel === 'comments'" x-cloak>
                            <label
                                class="flex min-h-11 cursor-pointer items-center justify-between gap-4 rounded-2xl bg-white/5 py-2 pl-4 pr-3 text-gray-100">
                                Me prévenir par e-mail des nouveaux commentaires
                                <input type="checkbox" role="switch" wire:model.live="isSubscribed"
                                    class="peer sr-only">
                                <span aria-hidden="true"
                                    class="peer-checked:bg-primary-500 relative h-7 w-12 shrink-0 rounded-full bg-white/25 transition-colors after:absolute after:left-1 after:top-1 after:size-5 after:rounded-full after:bg-white after:shadow-sm after:transition-transform peer-checked:after:translate-x-5 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-white"></span>
                            </label>

                            @if ($this->comments->isEmpty())
                                <p class="mt-6 text-gray-300">Pas encore de commentaire. Soyez le premier à écrire un mot
                                    sur cette photo !</p>
                            @else
                                <ol class="mt-6 flex flex-col gap-5">
                                    @foreach ($this->comments as $comment)
                                        <li wire:key="photo-drawer-comment-{{ $comment->id }}" class="flex gap-3">
                                            @if ($comment->user)
                                                <x-user-avatar :user="$comment->user" class="size-10" />
                                            @else
                                                <span aria-hidden="true"
                                                    class="size-10 shrink-0 rounded-full bg-white/10"></span>
                                            @endif
                                            <div class="min-w-0 flex-1">
                                                <div class="rounded-3xl rounded-tl-md bg-white/10 px-4 py-3">
                                                    <p class="flex flex-wrap items-baseline gap-x-3">
                                                        <span
                                                            class="wrap-break-word font-bold text-white">{{ $comment->user?->name ?? 'Ancien matelot' }}</span>
                                                        <time datetime="{{ $comment->created_at->toIso8601String() }}"
                                                            class="text-sm text-gray-300">
                                                            {{ $comment->created_at->isoFormat('D MMMM YYYY') }}
                                                        </time>
                                                    </p>
                                                    <p class="wrap-break-word mt-1 whitespace-pre-line text-gray-100">
                                                        {{ $comment->content }}</p>
                                                </div>

                                                @can('delete', $comment)
                                                    @if ($commentIdToDelete === $comment->id)
                                                        <div
                                                            class="mt-2 rounded-2xl bg-red-950/60 px-4 py-3 ring-1 ring-inset ring-red-400/40">
                                                            <p class="font-semibold text-red-100">Supprimer ce commentaire ?
                                                            </p>
                                                            <div class="mt-3 flex flex-wrap gap-3">
                                                                <button type="button"
                                                                    wire:click="deleteComment({{ $comment->id }})"
                                                                    class="{{ $focusRing }} inline-flex min-h-11 items-center rounded-full bg-red-700 px-5 py-2 font-semibold text-white transition-colors hover:bg-red-800">
                                                                    Oui, le supprimer
                                                                </button>
                                                                <button type="button" wire:click="cancelCommentDeletion"
                                                                    class="{{ $secondaryButton }}">
                                                                    Non, le garder
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <button type="button"
                                                            wire:click="confirmCommentDeletion({{ $comment->id }})"
                                                            class="{{ $quietButton }} mt-1">
                                                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="1.75"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                class="size-4 shrink-0">
                                                                <path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3" />
                                                            </svg>
                                                            Supprimer
                                                        </button>
                                                    @endif
                                                @endcan
                                            </div>
                                        </li>
                                    @endforeach
                                </ol>
                            @endif
                        </div>

                        {{-- Partager : un lien pour qui n'a pas de compte, ou celui de la galerie pour un membre --}}
                        <div x-show="panel === 'share'" x-cloak class="flex flex-col gap-8">
                            <section aria-labelledby="photo-drawer-share-guest">
                                <h3 id="photo-drawer-share-guest" class="text-lg font-bold text-white">Avec une personne
                                    sans compte</h3>
                                <p class="mt-2 leading-relaxed text-gray-300">
                                    Le lien montre cette photo seule, sans le reste de la galerie. Toute personne qui
                                    l'a pourra la voir et la télécharger pendant
                                    {{ App\Models\Share::DEFAULT_EXPIRY_DAYS }}
                                    jours.
                                </p>
                                @can('share', $photo)
                                    <button type="button" wire:click="createShareLink" wire:loading.attr="disabled"
                                        wire:target="createShareLink"
                                        class="{{ $shareLink ? $secondaryButton : $primaryButton }} mt-4">
                                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                                            class="size-5 shrink-0">
                                            <path d="M10 14a4.5 4.5 0 0 0 6.4 0l3-3a4.5 4.5 0 0 0-6.4-6.4l-1 1" />
                                            <path d="M14 10a4.5 4.5 0 0 0-6.4 0l-3 3a4.5 4.5 0 0 0 6.4 6.4l1-1" />
                                        </svg>
                                        {{ $shareLink ? 'Créer un autre lien' : 'Créer un lien de partage' }}
                                    </button>
                                @endcan
                                @if ($shareLink)
                                    <x-share-link :url="$shareLink['url']" dark class="mt-5"
                                        wire:key="photo-drawer-share-{{ $shareLink['url'] }}">
                                        <p class="mt-2 text-gray-300">Valable jusqu'au {{ $shareLink['expiresAt'] }}.</p>
                                    </x-share-link>
                                @endif
                            </section>

                            <section aria-labelledby="photo-drawer-share-member" class="border-t border-white/10 pt-6">
                                <h3 id="photo-drawer-share-member" class="text-lg font-bold text-white">Avec un membre
                                    du club</h3>
                                <p class="mt-2 leading-relaxed text-gray-300">Une fois connecté, il retrouve la photo
                                    dans la galerie.</p>
                                <x-share-link :url="route('photos.show', $photo)" label="Lien de la galerie" dark class="mt-4"
                                    wire:key="photo-drawer-member-link-{{ $photo->id }}" />
                            </section>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Champ du commentaire, toujours en bas du panneau --}}
            @can('create', App\Models\Comment::class)
                <form x-show="panel === 'comments'" x-cloak x-on:submit.prevent="postComment()"
                    class="border-t border-white/10 px-5 pb-[max(1rem,env(safe-area-inset-bottom))] pt-3">
                    <label for="photo-drawer-comment" class="sr-only">Votre commentaire</label>
                    <textarea id="photo-drawer-comment" wire:model="comment" rows="2" maxlength="2000" required
                        placeholder="Écrire un commentaire…" x-bind:disabled="!$wire.photoId"
                        x-on:keydown.ctrl.enter.prevent="postComment()" x-on:keydown.meta.enter.prevent="postComment()"
                        class="{{ $field }} max-h-40 min-h-14"></textarea>
                    @error('comment')
                        <p class="mt-2 text-red-300">{{ $message }}</p>
                    @enderror
                    <div class="mt-3 flex justify-end">
                        <button type="submit" x-bind:disabled="posting || $wire.photoId !== photoId"
                            class="{{ $primaryButton }}">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0">
                                <path d="M21 3 10 14M21 3l-7 18-4-7-7-4 18-7Z" />
                            </svg>
                            Publier le commentaire
                        </button>
                    </div>
                </form>
            @endcan
        </section>
    @endteleport
</div>
