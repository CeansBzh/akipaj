<x-app-layout>
    <h1 class="text-center">Uploader des photos</h1>
    <form class="mx-auto max-w-lg" action="{{ route('photos.store') }}" method="POST" enctype="multipart/form-data">
        @if(Session::has('success'))
        <div class="text-green-600">
            {{ Session::get('success') }}
        </div>
        @endif
        @csrf

        <x-drag-and-drop class="mb-1" />
        @error('files')
        <div class="text-red-500 mt-2 text-sm">
            {{ $message }}
        </div>
        @enderror
        @error('files.*')
        <div class="text-red-500 mt-2 text-sm">
            {{ $message }}
        </div>
        @enderror
        <hr class="mt-4 border border-black">
        <div class="my-4">
            <label for="album" class="sr-only">Liste des albums de photos</label>
            <select name="album" id="album"
                class="bg-gray-100 border-2 w-full p-4 rounded-lg @error('photo') border-red-500 @enderror">
                <option hidden disabled selected value>Lier les photos à un album</option>
                <optgroup label="Animaux à 4-jambes">
                    <option value="Chien">Chien</option>
                    <option value="chat">Chat</option>
                    <option value="hamster" disabled>Hamster</option>
                </optgroup>
                <optgroup label="Animaux volants">
                    <option value="perroquet">Perroquet</option>
                    <option value="macaw">Macaw</option>
                    <option value="albatros">Albatros</option>
                </optgroup>
            </select>
            @error('album')
            <div class="text-red-500 mt-2 text-sm">
                {{ $message }}
            </div>
            @enderror
        </div>
        <div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-3 rounded font-medium w-full">Envoyer</button>
        </div>
    </form>
</x-app-layout>