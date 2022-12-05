<div>
    <x-lightbox name="photo-lightbox" :photo="$photo">
        <x-slot name="image">
            <img class="max-h-screen block w-full object-contain" src="{{ $photo->path }}"
                alt="{{ isset($photo->legend) ? substr($photo->legend, 0, 125) : 'Photo sans légende' }}">
        </x-slot>

        <div class="text-white text-center text-sm">
            <p>Photo par - <span class="font-bold">{{ $photo->user->name }}</span></p>
            <p>{{ $photo->title }}</p>
            <a href="{{ route('photos.show', $photo) }}" class="underline font-bold">Voir les détails et commentaires</a>
        </div>
    </x-lightbox>
</div>