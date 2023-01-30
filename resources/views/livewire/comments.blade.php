<div class="w-full">
    <div class="mb-6 flex flex-col justify-between sm:flex-row">
        <h4 class="text-lg">Commentaires</h4>
        <livewire:thread-subscribe-button key="{{ now() }}" :commentable="$commentable" />
    </div>
    @foreach ($comments as $comment)
        <div class="relative mb-8 grid grid-cols-1 gap-3 rounded-lg border bg-white p-4 shadow-lg">
            <div class="relative flex gap-4">
                <img src="{{ $comment->user->profile_picture_path ?? Vite::asset('resources/images/default-pfp.png') }}"
                    class="relative -top-8 -mb-4 h-20 w-20 rounded-lg border bg-white"
                    alt="Photo de profil de {{ $comment->user->name }}" loading="lazy">
                <div class="flex w-full flex-col">
                    <div class="flex flex-row justify-between">
                        <p class="relative overflow-hidden truncate whitespace-nowrap text-xl">
                            {{ $comment->user->name }}
                        </p>
                        @can('delete', $comment)
                            <div class="text-sm text-gray-400">
                                @can('update', $comment)
                                    <button wire:click="setCommentToUpdate({{ $comment->id }})"
                                        class="hover:text-gray-600">Éditer</button>
                                    <span class="text-xs">•</span>
                                @endcan
                                <button wire:click="setCommentToDestroy({{ $comment->id }})"
                                    class="hover:text-red-600">Supprimer</button>
                            </div>
                        @endcan
                    </div>
                    <p class="text-sm text-gray-400">{{ $comment->created_at->diffForHumans() }}</p>
                </div>
            </div>
            <div class="-mt-4">
                @if ($commentIdToDestroy === $comment->id)
                    <div class="text-center">
                        <p class="pb-1">Voulez-vous vraiment supprimer ce commentaire ?</p>
                        <button wire:click="destroy({{ $comment->id }})"
                            class="w-32 rounded border bg-red-800 px-4 py-1 text-white hover:bg-red-600">Oui</button>
                        <button wire:click="setCommentToDestroy(null)"
                            class="w-32 rounded border bg-gray-600 px-4 py-1 text-white hover:bg-gray-500">Non</button>
                    </div>
                @elseif($commentIdToUpdate === $comment->id)
                    <form wire:submit.prevent="update({{ $comment->id }})">
                        <div class="mb-4 w-full rounded-lg border border-gray-200 bg-gray-50">
                            <div class="rounded-t-lg bg-white py-2 px-4">
                                <label for="newContent" class="sr-only">Votre commentaire</label>
                                <textarea id="newContent" wire:model.defer="newContent" x-data="{
                                    resize: () => {
                                        $el.style.height = '5px';
                                        $el.style.height = $el.scrollHeight + 'px'
                                    }
                                }" x-init="resize()"
                                    @input="resize()" name="newContent"
                                    class="min-h-[36px] w-full border-0 bg-white px-0 text-sm text-gray-900 focus:ring-0"
                                    placeholder="Écrivez votre commentaire..." required></textarea>
                            </div>
                            @error('newContent')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                            <div class="flex items-center justify-start border-t py-1 px-3">
                                <button type="submit"
                                    class="inline-flex items-center rounded-lg bg-blue-500 py-2 px-4 text-center text-sm font-medium text-white hover:bg-blue-600 focus:ring-4 focus:ring-blue-200">
                                    Envoyer
                                </button>
                                <button type="button" wire:click="setCommentToUpdate(null)"
                                    class="ml-3 inline-flex items-center rounded-lg bg-gray-500 py-2 px-4 text-center text-sm font-medium text-white hover:bg-gray-600 focus:ring-4 focus:ring-blue-200">Annuler</button>
                            </div>
                        </div>
                    </form>
                @else
                    <p class="text-gray-500">{{ $comment->content }}</p>
                @endif
            </div>
        </div>
    @endforeach

    <form wire:submit.prevent="store">
        <div class="mb-4 w-full rounded-lg border border-gray-200 bg-gray-50">
            <div class="rounded-t-lg bg-white py-2 px-4">
                <label for="content" class="sr-only">Votre commentaire</label>
                <textarea id="content" wire:model.defer="content" x-data="{
                    resize: () => {
                        $el.style.height = '5px';
                        $el.style.height = $el.scrollHeight + 'px'
                    }
                }" x-init="resize()" @input="resize()"
                    name="content" rows="1" class="min-h-[36px] w-full border-0 bg-white px-0 text-sm text-gray-900 focus:ring-0"
                    placeholder="Écrivez votre commentaire..." required></textarea>
            </div>
            @error('content')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <div class="flex items-center justify-between border-t py-1 px-3">
                <button type="submit"
                    class="inline-flex items-center rounded-lg bg-blue-500 py-2 px-4 text-center text-sm font-medium text-white hover:bg-blue-600 focus:ring-4 focus:ring-blue-200">
                    Envoyer
                </button>
            </div>
        </div>
    </form>
</div>
