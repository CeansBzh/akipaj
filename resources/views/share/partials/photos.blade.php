{{-- Vignettes d'un album partagé : chacune ouvre la visionneuse (resources/js/shared-photos.js) --}}
@foreach ($photos as $photo)
    @php
        [$width, $height] = $photo->viewerSize();
        $fileUrl = route('shares.file', [$share, $photo]);
    @endphp
    <a href="{{ $fileUrl }}" data-shared-photo data-pswp-src="{{ $fileUrl }}"
        data-pswp-width="{{ $width }}" data-pswp-height="{{ $height }}" data-pswp-cropped="true"
        data-download-name="{{ $photo->downloadName() }}" style="grid-row-end: span {{ $photo->masonryRows() }}"
        class="group relative block focus-visible:outline-none">
        <img src="{{ route('shares.thumbnail', [$share, $photo]) }}" alt="{{ $photo->altText(withUploader: false) }}"
            width="{{ $photo->thumb_width }}" height="{{ $photo->thumb_height }}"
            loading="{{ $loop->index < $eagerCount ? 'eager' : 'lazy' }}" decoding="async"
            class="group-focus-visible:outline-primary-700 absolute inset-x-0 top-0 h-[calc(100%-var(--masonry-gap))] w-full bg-gray-200 object-cover group-focus-visible:outline-4 group-focus-visible:-outline-offset-4">
    </a>
@endforeach
