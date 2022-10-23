<div class="w-full">
    <h4 class="mb-5 text-lg">Commentaires</h4>
    @foreach($comments as $comment)
    <div class="relative grid grid-cols-1 gap-3 p-4 mb-8 border rounded-lg bg-white shadow-lg">
        <div class="relative flex gap-4">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/7/7e/Circle-icons-profile.svg/512px-Circle-icons-profile.svg.png"
                class="relative rounded-lg -top-8 -mb-4 bg-white border h-20 w-20"
                alt="Photo de profil de {{ $comment->user->name }}" loading="lazy">
            <div class="flex flex-col w-full">
                <div class="flex flex-row justify-between">
                    <p class="relative text-xl whitespace-nowrap truncate overflow-hidden">{{ $comment->user->name }}
                    </p>
                    @can('delete', $comment)
                    <div class="text-gray-400 text-sm">
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
                <p class="text-gray-400 text-sm">{{ $comment->created_at->diffForHumans() }}</p>
            </div>
        </div>
        <div class="-mt-4">
            @if($commentIdToDestroy === $comment->id)
            <div class="text-center">
                <p class="pb-1">Voulez-vous vraiment supprimer ce commentaire ?</p>
                <button wire:click="destroy({{ $comment->id }})"
                    class="bg-red-800 text-white w-32 px-4 py-1 hover:bg-red-600 rounded border">Oui</button>
                <button wire:click="setCommentToDestroy(null)"
                    class="bg-gray-600 text-white w-32 px-4 py-1 hover:bg-gray-500 rounded border">Non</button>
            </div>
            @elseif($commentIdToUpdate === $comment->id)
            <form wire:submit.prevent="update({{ $comment->id }})">
                <div class="mb-4 w-full bg-gray-50 rounded-lg border border-gray-200">
                    <div class="py-2 px-4 bg-white rounded-t-lg">
                        <label for="newContent" class="sr-only">Votre commentaire</label>
                        <textarea id="newContent" wire:model.defer="newContent" name="newContent"
                            class="px-0 min-h-[36px] w-full text-sm text-gray-900 bg-white border-0 focus:ring-0"
                            placeholder="Écrivez votre commentaire..." required></textarea>
                    </div>
                    @error('newContent') <div class="alert alert-danger">{{ $message }}</div> @enderror
                    <div class="flex justify-start items-center py-1 px-3 border-t">
                        <button type="submit"
                            class="inline-flex items-center py-2 px-4 text-sm font-medium text-center text-white bg-blue-500 rounded-lg focus:ring-4 focus:ring-blue-200 hover:bg-blue-600">
                            Envoyer
                        </button>
                        <button type="button" wire:click="setCommentToUpdate(null)"
                            class="inline-flex items-center ml-3 py-2 px-4 text-sm font-medium text-center text-white bg-gray-500 rounded-lg focus:ring-4 focus:ring-blue-200 hover:bg-gray-600">Annuler</button>
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
        <div class="mb-4 w-full bg-gray-50 rounded-lg border border-gray-200">
            <div class="py-2 px-4 bg-white rounded-t-lg">
                <label for="content" class="sr-only">Votre commentaire</label>
                <textarea id="content" wire:model.defer="content" name="content" rows="1"
                    class="px-0 min-h-[36px] w-full text-sm text-gray-900 bg-white border-0 focus:ring-0"
                    placeholder="Écrivez votre commentaire..." required></textarea>
            </div>
            @error('content') <div class="alert alert-danger">{{ $message }}</div> @enderror
            <div class="flex justify-between items-center py-1 px-3 border-t">
                <button type="submit"
                    class="inline-flex items-center py-2 px-4 text-sm font-medium text-center text-white bg-blue-500 rounded-lg focus:ring-4 focus:ring-blue-200 hover:bg-blue-600">
                    Envoyer
                </button>
            </div>
        </div>
    </form>
</div>