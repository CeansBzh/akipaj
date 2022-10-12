<x-app-layout>
    <h1>Photos du site</h1>

    <table class="table-auto border-separate border-2 border-black">
        <thead>
            <tr>
                <th class="bg-blue-100 border text-left px-8 py-4">Image</th>
                <th class="bg-blue-100 border text-left px-8 py-4">Titre</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($photos as $photo)
            <tr>
                <td>
                    <img src="{{ $photo->path }}" class="max-w-xs">
                </td>
                <td>{{ $photo->title }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $photos->links() }}
</x-app-layout>