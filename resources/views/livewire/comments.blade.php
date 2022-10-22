<div class="w-full">
    @foreach($comments as $comment)
    <div class="relative grid grid-cols-1 gap-4 p-4 mb-8 border rounded-lg bg-white shadow-lg">
        <div class="relative flex gap-4">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/7/7e/Circle-icons-profile.svg/512px-Circle-icons-profile.svg.png"
                class="relative rounded-lg -top-8 -mb-4 bg-white border h-20 w-20"
                alt="Photo de profil de {{ $comment->user->name }}" loading="lazy">
            <div class="flex flex-col w-full">
                <div class="flex flex-row justify-between">
                    <p class="relative text-xl whitespace-nowrap truncate overflow-hidden">{{ $comment->user->name }}
                    </p>
                    @if($comment->user_id === Auth::id())
                    {{-- TODO Utiliser policy --}}
                    @if($commentIdToDestroy === $comment->id)
                    <button wire:click="destroy({{ $comment->id }})"
                        class="bg-red-800 text-white w-32 px-4 py-1 hover:bg-red-600 rounded border">Sure?</button>
                    @else
                    <button wire:click="setCommentToDestroy({{ $comment->id }})"
                        class="bg-gray-600 text-white w-32 px-4 py-1 hover:bg-red-600 rounded border">Delete</button>
                    @endif
                    @endif
                </div>
                <p class="text-gray-400 text-sm">{{ $comment->created_at->diffForHumans() }}</p>
            </div>
        </div>
        <p class="-mt-4 text-gray-500">{{ $comment->content }}</p>
    </div>
    @endforeach

    <div>
        <form wire:submit.prevent="store">
            <textarea wire:model="content" name="content" id="content" cols="50" rows="1"></textarea>
            <button type="submit">Envoyer</button>
        </form>
    </div>
</div>