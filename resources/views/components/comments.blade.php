<div class="w-full">
    @foreach($comments as $comment)
    <div class="w-1/2">
        <div>
            <strong>{{ $comment->user->name }}</strong>
            <span>{{ $comment->created_at->diffForHumans() }}</span>
        </div>
        <p>
            {{ $comment->content }}
        </p>
    </div>
    @endforeach

    <div>
        <form action="{{ route('comments.store', ['commentable_type' => $commentable::class, 'commentable_id' => $commentable->id]) }}" method="POST">
            @csrf
            <textarea name="content" id="content" cols="50" rows="1"></textarea>
            <button type="submit">Envoyer</button>
        </form>
    </div>
</div>