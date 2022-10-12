<x-app-layout>
    <h1>Uploader une photo</h1>
    <form class="mx-auto max-w-sm" action="{{ route('photos.store') }}" method="POST" enctype="multipart/form-data">
        @if(Session::has('success'))
        <div class="text-green-600">
            {{ Session::get('success') }}
        </div>
        @endif
        @csrf
        <div class="mb-4">
            <label for="title" class="sr-only">Titre</label>
            <input type="text" name="title" id="title" placeholder="Titre de la photo"
                class="bg-gray-100 border-2 w-full p-4 rounded-lg @error('title') border-red-500 @enderror"
                value="{{ old('title') }}">
            @error('title')
            <div class="text-red-500 mt-2 text-sm">
                {{ $message }}
            </div>
            @enderror
        </div>
        <div class="mb-4">
            <label for="photo" class="sr-only">Photo</label>
            <input type="file" name="photo" id="photo" placeholder="Photo"
                class="bg-gray-100 border-2 w-full p-4 rounded-lg @error('photo') border-red-500 @enderror"
                value="{{ old('photo') }}">
            @error('photo')
            <div class="text-red-500 mt-2 text-sm">
                {{ $message }}
            </div>
            @enderror
        </div>
        <hr class="border border-black">
        <div class="my-4">
            <label for="legend" class="sr-only">Légende</label>
            <textarea name="legend" id="legend" cols="30" rows="5" placeholder="Légende de la photo" maxlength="2048"
                class="bg-gray-100 border-2 w-full p-4 rounded-lg @error('legend') border-red-500 @enderror"
                value="{{ old('legend') }}"></textarea>
            @error('legend')
            <div class="text-red-500 mt-2 text-sm">
                {{ $message }}
            </div>
            @enderror
        </div>
        <div class="my-4">
            <label for="taken" class="sr-only">Date de prise de la photo</label>
            <input type="date" name="taken" id="taken" placeholder="Date de prise de la photo"
                class="bg-gray-100 border-2 w-full p-4 rounded-lg @error('taken') border-red-500 @enderror"
                value="{{ old('taken') }}">
                <p>Utile lorsque la date de la photo ne figure pas dans son fichier.</p>
            @error('taken')
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