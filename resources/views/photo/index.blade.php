<x-app-layout>
    <h1>Photos du site</h1>
    <a class="m-1 px-4 py-2 text-blue-100 no-underline bg-blue-500 rounded hover:bg-blue-600 hover:underline hover:text-blue-200" href="{{ route('photos.create') }}">Ajouter une photo</a>

    @if(Session::has('success'))
    <div class="text-green-600">
        {{ Session::get('success') }}
    </div>
    @endif

    <table class="table-auto border-separate border-2 border-black">
        <thead>
            <tr>
                <th class="bg-blue-100 border text-left px-8 py-4">Image</th>
                <th class="bg-blue-100 border text-left px-8 py-4">Titre</th>
                <th class="bg-blue-100 border text-left px-8 py-4">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($photos as $photo)
            <tr>
                <td>
                    <img src="{{ $photo->path }}" class="max-w-xs">
                </td>
                <td>{{ $photo->title }}</td>
                <td>
                    <a href="{{ route('photos.show', $photo->id) }}" class="">Voir</a>
                    <a href="{{ route('photos.edit', $photo->id) }}" class="">Modifier</a>
                    <form method="POST" action="{{ route('photos.destroy', $photo->id) }}">
                        @csrf
                        @method('DELETE')
                        <input type="submit" class="cursor-pointer" value="Supprimer">
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $photos->links() }}
</x-app-layout>