@php
    // Hauteur de la vignette en lignes de la mosaïque : une colonne est large de 32 lignes (voir .photo-masonry dans app.css)
    $thumbWidth = $photo->thumb_width ?: 4;
    $thumbHeight = $photo->thumb_height ?: 3;
    $rows = (int) min(96, max(10, round((32 * $thumbHeight) / $thumbWidth)));
    // Taille de la photo d'origine, dont la visionneuse a besoin pour la zoomer ; à défaut, celle de la vignette agrandie
    $width = $photo->width ?: $thumbWidth * 4;
    $height = $photo->height ?: $thumbHeight * 4;
    $uploaderName = $photo->user?->name;
    $alt = $photo->legend
        ? \Illuminate\Support\Str::limit($photo->legend, 140)
        : trim(
            'Photo' .
                ($uploaderName ? ' de ' . $uploaderName : '') .
                ($photo->taken_at ? ', ' . $photo->taken_at->isoFormat('D MMMM YYYY') : ''),
        );
@endphp

{{--
    Lien de la photo, qui ouvre la galerie sur elle : la visionneuse s'ouvre directement (resources/js/gallery.js),
    sauf ouverture dans un nouvel onglet
--}}
<a href="{{ route('photos.show', $photo) }}" wire:key="photo-{{ $photo->id }}" data-gallery-photo
    data-photo-id="{{ $photo->id }}" data-comment-count="{{ $photo->comments_count ?? 0 }}"
    data-pswp-src="{{ $photo->path }}" data-pswp-width="{{ $width }}" data-pswp-height="{{ $height }}"
    data-pswp-cropped="true" data-uploader="{{ $photo->user_id }}"
    data-download-name="{{ pathinfo($photo->title, PATHINFO_FILENAME) ?: 'photo' }}.jpg"
    @can('update', $photo) data-edit-url="{{ route('photos.edit', $photo) }}" @endcan
    style="grid-row-end: span {{ $rows }}" class="group relative block focus-visible:outline-none">
    <img src="{{ $photo->thumb_path }}" alt="{{ $alt }}" width="{{ $photo->thumb_width }}"
        height="{{ $photo->thumb_height }}" loading="{{ $isEager ? 'eager' : 'lazy' }}" decoding="async"
        class="group-focus-visible:outline-primary-700 absolute inset-x-0 top-0 h-[calc(100%-var(--masonry-gap))] w-full bg-gray-200 object-cover group-focus-visible:outline-4 group-focus-visible:-outline-offset-4">
</a>
