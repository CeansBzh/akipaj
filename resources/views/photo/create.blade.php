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
        @if(!$albums->isEmpty())
        <div class="my-4 relative">
            <label for="album" class="sr-only">Liste des albums de photos</label>
            <select name="album" id="album"
                class="bg-gray-100 border-2 w-full p-4 pr-12 rounded-lg truncate @error('album') border-red-500 @enderror">
                <option hidden disabled selected>Lier les photos à un album ?</option>
                @foreach ($albums as $year => $albumPerYear)
                <optgroup label="{{ $year }}">
                    @foreach ($albumPerYear as $album)
                    <option value="{{ $album->id }}">{{ $album->title }}</option>
                    @endforeach
                </optgroup>
                @endforeach
            </select>
            <button type="button" id="clearBtn" class="absolute top-1/3 right-9 text-slate-500"><svg
                    xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-x">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg></button>
            @error('album')
            <div class="text-red-500 mt-2 text-sm">
                {{ $message }}
            </div>
            @enderror
        </div>
        <p>Ou créer un nouvel album :</p>
        @else
        <p class="mt-4">Vous pouvez créer un nouvel album et y associer les photos :</p>
        @endif
        <div class="my-4">
            <label for="albumTitle" class="sr-only">Titre</label>
            <input type="text" name="albumTitle" id="albumTitle" placeholder="Titre de l'album"
                class="bg-gray-100 border-2 w-full p-4 rounded-lg @error('albumTitle') border-red-500 @enderror"
                value="{{ old('albumTitle') }}">
            @error('albumTitle')
            <div class="text-red-500 mt-2 text-sm">
                {{ $message }}
            </div>
            @enderror
        </div>
        <div class="mb-4">
            <label for="albumDesc" class="sr-only">Description de l'album</label>
            <textarea name="albumDesc" id="albumDesc" cols="30" rows="5" placeholder="Description de l'album"
                maxlength="2048"
                class="bg-gray-100 border-2 w-full p-4 rounded-lg @error('albumDesc') border-red-500 @enderror">{{ old('albumDesc') }}</textarea>
            @error('albumDesc')
            <div class="text-red-500 mt-2 text-sm">
                {{ $message }}
            </div>
            @enderror
        </div>
        <div class="mb-4">
            <label for="albumDate">Date des photos</label>
            <input type="month" name="albumDate" id="albumDate" min="1950-01" max="{{ date('Y-m') }}"
                class="bg-gray-100 border-2 w-full p-4 rounded-lg @error('albumDate') border-red-500 @enderror"
                value="{{ old('albumDate') ?? now()->format('Y-m') }}">
            @error('albumDate')
            <div class="text-red-500 mt-2 text-sm">
                {{ $message }}
            </div>
            @enderror
        </div>
        <div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-3 rounded font-medium w-full">Envoyer</button>
        </div>
    </form>

    @if(!$albums->isEmpty())
    @push('scripts')
    <script type="text/javascript">
        let select = document.getElementById('album');
        let clearBtn = document.getElementById('clearBtn');
        clearBtn.addEventListener('click', function () {
            select.selectedIndex = 0;
        });
    </script>
    @endpush
    @endif
</x-app-layout>