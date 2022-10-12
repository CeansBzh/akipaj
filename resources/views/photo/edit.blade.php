<x-app-layout>
    <h1>Ma photo : {{ $photo->title }}</h1>
    <img class="max-w-xs" src="{{ $photo->path }}"
        alt="{{ isset($photo->legend) ? substr($photo->legend, 0, 125) : 'Photo sans légende' }}">

    <form class="mx-auto max-w-sm" action="{{ route('photos.update', $photo->id) }}" method="POST" enctype="multipart/form-data">
        @method('PATCH')
        @csrf
        <div class="mb-4">
            <label for="title" class="sr-only">Titre</label>
            <input type="text" name="title" id="title" placeholder="Nouveau titre"
                class="bg-gray-100 border-2 w-full p-4 rounded-lg @error('title') border-red-500 @enderror"
                value="{{ $photo->title }}">
            @error('title')
            <div class="text-red-500 mt-2 text-sm">
                {{ $message }}
            </div>
            @enderror
        </div>
        <hr class="border border-black">
        <div class="my-4">
            <label for="legend" class="sr-only">Légende</label>
            <textarea name="legend" id="legend" cols="30" rows="5" placeholder="Légende de la photo" maxlength="2048"
                class="bg-gray-100 border-2 w-full p-4 rounded-lg @error('legend') border-red-500 @enderror">{{ $photo->legend }}
            </textarea>
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
                value="{{ $photo->taken }}">
            <p>Utile lorsque la date de la photo ne figure pas dans son fichier.</p>
            @error('taken')
            <div class="text-red-500 mt-2 text-sm">
                {{ $message }}
            </div>
            @enderror
        </div>
        <div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-3 rounded font-medium w-full">Modifier</button>
        </div>
    </form>
</x-app-layout>