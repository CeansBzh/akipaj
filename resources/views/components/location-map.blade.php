@props(['latitude', 'longitude', 'zoom' => 11])

{{--
    Petite carte fixe centrée sur un point, sans bibliothèque : les tuiles d'OpenStreetMap autour du point,
    posées de sorte qu'il tombe au milieu du cadre, et une épingle par-dessus. Les tuiles ne se chargent qu'à l'affichage.
    Les coordonnées sont aussi écrites, à la manière des marins (degrés et minutes), avec un lien vers la carte complète.
--}}

@php
    $latitude = (float) $latitude;
    $longitude = (float) $longitude;
    $tileCount = 2 ** $zoom;

    // Position du point en tuiles (projection de Mercator utilisée par OpenStreetMap)
    $x = (($longitude + 180) / 360) * $tileCount;
    $latitudeRadians = deg2rad(max(-85, min(85, $latitude)));
    $y = ((1 - log(tan($latitudeRadians) + 1 / cos($latitudeRadians)) / M_PI) / 2) * $tileCount;
    $tileX = (int) floor($x);
    $tileY = (int) floor($y);
    $offsetX = ($x - $tileX) * 256;
    $offsetY = ($y - $tileY) * 256;

    // Les neuf tuiles autour du point, placées depuis le milieu du cadre
    $tiles = [];
    foreach ([-1, 0, 1] as $rowShift) {
        foreach ([-1, 0, 1] as $columnShift) {
            $row = $tileY + $rowShift;
            if ($row < 0 || $row >= $tileCount) {
                continue;
            }
            $column = ((($tileX + $columnShift) % $tileCount) + $tileCount) % $tileCount;
            $tiles[] = [
                'url' => "https://tile.openstreetmap.org/{$zoom}/{$column}/{$row}.png",
                'left' => round($columnShift * 256 - $offsetX),
                'top' => round($rowShift * 256 - $offsetY),
            ];
        }
    }

    $nautical = function (float $value, string $positive, string $negative): string {
        $degrees = (int) floor(abs($value));
        $minutes = str_pad(number_format((abs($value) - $degrees) * 60, 2, ',', ''), 5, '0', STR_PAD_LEFT);

        return $degrees . "°\u{202F}" . $minutes . "′\u{202F}" . ($value >= 0 ? $positive : $negative);
    };
    $mapUrl =
        'https://www.openstreetmap.org/?mlat=' .
        $latitude .
        '&mlon=' .
        $longitude .
        '#map=13/' .
        $latitude .
        '/' .
        $longitude;
@endphp

<div {{ $attributes }}>
    <div class="relative h-48 overflow-hidden rounded-2xl bg-gray-800">
        @foreach ($tiles as $tile)
            <img src="{{ $tile['url'] }}" alt="" width="256" height="256" loading="lazy" decoding="async"
                class="absolute size-64 max-w-none"
                style="left: calc(50% + {{ $tile['left'] }}px); top: calc(50% + {{ $tile['top'] }}px)">
        @endforeach
        <svg aria-hidden="true" viewBox="0 0 32 40"
            class="absolute left-1/2 top-1/2 h-10 w-8 -translate-x-1/2 -translate-y-full drop-shadow-md">
            <path d="M16 39s13-12.5 13-23A13 13 0 0 0 3 16c0 10.5 13 23 13 23Z" class="fill-primary-700 stroke-white"
                stroke-width="2" />
            <circle cx="16" cy="16" r="5" class="fill-white" />
        </svg>
        <p class="absolute bottom-0 right-0 rounded-tl-lg bg-white/85 px-1.5 text-xs text-gray-800">
            © <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener"
                class="underline">OpenStreetMap</a>
        </p>
    </div>
    <p class="mt-2 tabular-nums text-gray-100">
        {{ $nautical($latitude, 'N', 'S') }}, {{ $nautical($longitude, 'E', 'O') }}
    </p>
    <a href="{{ $mapUrl }}" target="_blank" rel="noopener"
        class="mt-1 inline-flex min-h-11 items-center gap-1.5 font-semibold text-white underline decoration-white/50 underline-offset-4 hover:decoration-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
        Ouvrir la carte
        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
            stroke-linecap="round" stroke-linejoin="round" class="size-4 shrink-0">
            <path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5" />
        </svg>
        <span class="sr-only">(nouvel onglet)</span>
    </a>
</div>
