@php
    [$width, $height] = $photo->viewerSize();
    $fileUrl = route('shares.file', [$share, $photo]);
@endphp

{{--
    Photo partagée seule : la visionneuse s'ouvre sur elle et ne se ferme pas (resources/js/shared-photos.js).
    Avant elle, ou sans JavaScript, la photo occupe déjà l'écran.
--}}
<x-share-layout title="Une photo du club" :image="$fileUrl" dark>
    <main>
        <h1 class="sr-only">Une photo du club Akipaj</h1>
        {{-- Un lien, dont PhotoSwipe lit les données data-pswp-* : sans JavaScript, il ouvre la photo en grand --}}
        <a href="{{ $fileUrl }}" data-shared-photo data-pswp-src="{{ $fileUrl }}"
            data-pswp-width="{{ $width }}" data-pswp-height="{{ $height }}"
            data-download-name="{{ $photo->downloadName() }}">
            <img src="{{ $fileUrl }}" alt="{{ $photo->altText(withUploader: false) }}" width="{{ $width }}"
                height="{{ $height }}" fetchpriority="high" class="fixed inset-0 size-full object-contain">
        </a>
    </main>

    @include('share.partials.lightbox-controls', ['canClose' => false])
</x-share-layout>
