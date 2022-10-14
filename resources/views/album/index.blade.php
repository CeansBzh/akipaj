<x-app-layout>
    <h1>Liste des albums</h1>

    @if(Session::has('success'))
    <div class="text-green-600">
        {{ Session::get('success') }}
    </div>
    @endif

    @foreach($albums as $album)
    <div>{{ $album->title }}</div>
    @endforeach

    {{ $albums->links() }}
</x-app-layout>