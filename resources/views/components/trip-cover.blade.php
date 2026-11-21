@props(['trip', 'sizes' => '100vw'])

{{--
    Image de couverture d'une sortie, ou l'image par défaut, décorative. Le navigateur choisit parmi ses copies
    réduites la plus petite assez large pour la largeur décrite par `sizes` : celle sur laquelle la photo est dessinée,
    parties coupées par `object-cover` comprises. Dans un emplacement haut, une photo en paysage déborde sur les côtés :
    compter alors sa largeur à partir de la hauteur, pour une photo en 3:2.
--}}

@php
    $coverSizes =
        $trip->imagePath === null
            ? [
                480 => Vite::asset('resources/images/trip-cover-480.webp'),
                720 => Vite::asset('resources/images/trip-cover-720.webp'),
            ]
            : $trip->coverSizes();
    $srcset = collect($coverSizes)->map(fn(string $url, int $width): string => "{$url} {$width}w")->implode(', ');
@endphp

<img src="{{ $trip->coverUrl(960) ?? end($coverSizes) }}"
    @if ($srcset) srcset="{{ $srcset }}" sizes="{{ $sizes }}" @endif {{ $attributes->merge(['alt' => '']) }}>
