{{--
    Commentaires d'un profil ou d'une photo, du plus ancien au plus récent, dans des bulles signées,
    puis le champ pour en écrire un. Chacun peut modifier ou supprimer les siens ; la suppression se confirme.
--}}

<div>
    <div class="flex flex-wrap items-center justify-between gap-x-6 gap-y-2">
        <h2 class="text-2xl font-bold text-gray-900">Commentaires</h2>
        <livewire:thread-subscribe-button :key="'subscribe-' . now()->timestamp" :commentable="$commentable" />
    </div>

    @if ($comments->isEmpty())
        <p class="mt-4 text-gray-700">Aucun commentaire pour l'instant.</p>
    @else
        <ol class="mt-6 flex flex-col gap-5">
            @foreach ($comments as $comment)
                <li wire:key="comment-{{ $comment->id }}" class="flex gap-3">
                    <x-user-avatar :user="$comment->user" class="size-10 sm:size-12" />
                    <div class="min-w-0 flex-1">
                        @if ($commentIdToUpdate === $comment->id)
                            <form wire:submit="update({{ $comment->id }})">
                                <label for="newContent" class="font-semibold text-gray-900">Modifier votre commentaire</label>
                                <textarea id="newContent" wire:model="newContent" rows="3" required
                                    class="mt-2 block min-h-24 w-full rounded-3xl border-0 bg-white px-4 py-3 text-gray-900 shadow-sm ring-1 ring-gray-300 ring-inset field-sizing-content focus:ring-2 focus:ring-primary-700"></textarea>
                                <x-input-error :messages="$errors->get('newContent')" class="mt-2" />
                                <div class="mt-3 flex flex-wrap gap-3">
                                    <button type="submit"
                                        class="inline-flex min-h-11 items-center rounded-full bg-primary-700 px-5 py-2 font-semibold text-white transition-colors hover:bg-primary-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700">
                                        Enregistrer
                                    </button>
                                    <button type="button" wire:click="setCommentToUpdate(null)"
                                        class="inline-flex min-h-11 items-center rounded-full px-5 py-2 font-semibold text-gray-800 ring-1 ring-gray-300 ring-inset transition-colors hover:bg-gray-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700">
                                        Annuler
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="rounded-3xl rounded-tl-md bg-gray-100 px-4 py-3">
                                <p class="flex flex-wrap items-baseline gap-x-3">
                                    <a href="{{ route('profile.show', $comment->user->name) }}"
                                        class="font-bold break-words text-gray-900 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700">
                                        {{ $comment->user->name }}
                                    </a>
                                    <time datetime="{{ $comment->created_at->toIso8601String() }}" class="text-sm text-gray-600">
                                        {{ ucfirst($comment->created_at->isoFormat('dddd D MMMM YYYY')) }}
                                    </time>
                                </p>
                                <p class="mt-1 break-words whitespace-pre-line text-gray-800">{{ $comment->content }}</p>
                            </div>

                            @if ($commentIdToDestroy === $comment->id)
                                <div class="mt-2 rounded-2xl bg-red-50 px-4 py-3 ring-1 ring-red-200 ring-inset">
                                    <p class="font-semibold text-red-800">Supprimer ce commentaire ?</p>
                                    <div class="mt-3 flex flex-wrap gap-3">
                                        <button type="button" wire:click="destroy({{ $comment->id }})"
                                            class="inline-flex min-h-11 items-center rounded-full bg-red-700 px-5 py-2 font-semibold text-white transition-colors hover:bg-red-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-700">
                                            Oui, le supprimer
                                        </button>
                                        <button type="button" wire:click="setCommentToDestroy(null)"
                                            class="inline-flex min-h-11 items-center rounded-full bg-white px-5 py-2 font-semibold text-gray-800 ring-1 ring-gray-300 ring-inset transition-colors hover:bg-gray-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700">
                                            Non, le garder
                                        </button>
                                    </div>
                                </div>
                            @else
                                @can('delete', $comment)
                                    <div class="mt-1 flex flex-wrap gap-x-1">
                                        @can('update', $comment)
                                            <button type="button" wire:click="setCommentToUpdate({{ $comment->id }})"
                                                class="inline-flex min-h-11 items-center gap-1.5 rounded-full px-3 font-semibold text-gray-700 transition-colors hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-2 focus-visible:outline-primary-700">
                                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4 shrink-0">
                                                    <path d="M4 20h4L19 9a2.8 2.8 0 0 0-4-4L4 16v4Z" />
                                                    <path d="m13.5 6.5 4 4" />
                                                </svg>
                                                Modifier
                                            </button>
                                        @endcan
                                        <button type="button" wire:click="setCommentToDestroy({{ $comment->id }})"
                                            class="inline-flex min-h-11 items-center gap-1.5 rounded-full px-3 font-semibold text-gray-700 transition-colors hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-2 focus-visible:outline-primary-700">
                                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4 shrink-0">
                                                <path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3" />
                                            </svg>
                                            Supprimer
                                        </button>
                                    </div>
                                @endcan
                            @endif
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    @endif

    @can('create', App\Models\Comment::class)
        <form wire:submit="store" class="mt-8 flex gap-3">
            <x-user-avatar :user="auth()->user()" class="size-10 sm:size-12" />
            <div class="min-w-0 flex-1">
                <label for="content" class="font-semibold text-gray-900">Votre commentaire</label>
                <textarea id="content" wire:model="content" rows="3" required placeholder="Écrivez votre commentaire…"
                    class="mt-2 block min-h-24 w-full rounded-3xl border-0 bg-white px-4 py-3 text-gray-900 shadow-sm ring-1 ring-gray-300 ring-inset field-sizing-content placeholder:text-gray-500 focus:ring-2 focus:ring-primary-700"></textarea>
                <x-input-error :messages="$errors->get('content')" class="mt-2" />
                <div class="mt-3 flex justify-end">
                    <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex min-h-11 items-center gap-2 rounded-full bg-primary-700 px-5 py-2 font-semibold text-white transition-colors hover:bg-primary-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700 disabled:cursor-wait disabled:opacity-60">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0">
                            <path d="M21 3 10 14M21 3l-7 18-4-7-7-4 18-7Z" />
                        </svg>
                        Publier le commentaire
                    </button>
                </div>
            </div>
        </form>
    @endcan
</div>
