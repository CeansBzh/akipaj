@php
    [$width, $height] = $photo->viewerSize();
@endphp

{{--
    Lien de la photo, qui ouvre la galerie sur elle : la visionneuse s'ouvre directement (resources/js/gallery.js),
    sauf ouverture dans un nouvel onglet
--}}
<a href="{{ route('photos.show', $photo) }}" wire:key="photo-{{ $photo->id }}" data-gallery-photo
    data-photo-id="{{ $photo->id }}" data-comment-count="{{ $photo->comments_count ?? 0 }}"
    data-pswp-src="{{ $photo->path }}" data-pswp-width="{{ $width }}" data-pswp-height="{{ $height }}"
    data-pswp-cropped="true" data-uploader="{{ $photo->user_id }}" data-download-name="{{ $photo->downloadName() }}"
    @can('update', $photo) data-edit-url="{{ route('photos.edit', $photo) }}" @endcan
    style="grid-row-end: span {{ $photo->masonryRows() }}" class="group relative block focus-visible:outline-none">
    <img src="{{ $photo->thumb_path }}" alt="{{ $photo->altText() }}" width="{{ $photo->thumb_width }}"
        height="{{ $photo->thumb_height }}" loading="{{ $isEager ? 'eager' : 'lazy' }}" decoding="async"
        class="group-focus-visible:outline-primary-700 absolute inset-x-0 top-0 h-[calc(100%-var(--masonry-gap))] w-full bg-gray-200 object-cover group-focus-visible:outline-4 group-focus-visible:-outline-offset-4">
</a>
