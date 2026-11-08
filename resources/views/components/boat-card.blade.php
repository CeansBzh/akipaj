@props(['boat', 'index' => 0])

{{--
    Bateau d'une sortie : un voilier dessiné au mouillage, bercé par la houle, puis sa fiche.
    Le gréement suit le type du bateau (catamaran, ketch, sinon sloop) et la coque change de couleur d'un bateau à l'autre.
    Le dessin est décoratif : le nom et les caractéristiques sont écrits dans la fiche.
--}}

@php
    $boatType = Str::lower((string) $boat->type);
    $rig = match (true) {
        Str::contains($boatType, 'cata') => 'catamaran',
        Str::contains($boatType, ['ketch', 'yawl', 'goélette', 'goelette']) => 'ketch',
        default => 'sloop',
    };

    // Peintures de coque : marine, blanche, bordeaux, vert anglais
    $paints = [
        ['hull' => '#1d3557', 'shade' => '#0f1f38', 'trim' => 'text-white'],
        ['hull' => '#f8fafc', 'shade' => '#cbd5e1', 'trim' => 'text-primary-800'],
        ['hull' => '#7a2833', 'shade' => '#4a141c', 'trim' => 'text-white'],
        ['hull' => '#1f4d3a', 'shade' => '#0f2e22', 'trim' => 'text-white'],
    ];
    $paint = $paints[$index % count($paints)];

    // Dessin en unités du viewBox (400 × 260) : la mer commence à l'horizon, en y = 194
    $drawing = match ($rig) {
        'catamaran' => [
            'hull' => 'M90 190 L318 186 L308 206 Q205 212 100 207 Z',
            'farHull' => 'M106 184 L328 180 L320 198 Q216 203 114 199 Z',
            'bootStripe' => 'M98 202 Q205 208 311 201',
            'sheerStripe' => 'M92 193 L316 189',
            'cabin' => 'M128 188 L142 167 L252 165 L272 186 Z',
            'windows' => 'M146 171 L250 169.5 L260 179 L140 180.5 Z',
            'masts' => [['x' => 200, 'foot' => 166, 'head' => 30]],
            'booms' => ['M200 150 L132 153'],
            'mainsails' => ['M201 32 L183 36 Q150 96 133 150 L200 148 Z'],
            'jib' => 'M201 46 L312 184 Q270 172 222 160 Q213 102 201 46 Z',
            'stays' => ['M200 30 L318 186', 'M200 30 L94 190'],
            'name' => ['x' => 205, 'y' => 201],
            'bow' => 308,
            'stern' => 100,
        ],
        'ketch' => [
            'hull' => 'M96 186 Q210 194 318 180 L300 206 Q205 216 108 206 Z',
            'bootStripe' => 'M104 200 Q205 208 304 198',
            'sheerStripe' => 'M100 190.5 Q210 198.5 314 184.5',
            'cabin' => 'M152 189 L161 178 L226 177 L235 188 Z',
            'windows' => null,
            'masts' => [['x' => 208, 'foot' => 187, 'head' => 44], ['x' => 134, 'foot' => 188, 'head' => 92]],
            'booms' => ['M208 167 L150 170', 'M134 170 L104 173'],
            'mainsails' => ['M209 48 Q178 102 151 167 L208 165 Z', 'M135 96 Q114 132 105 171 L134 169 Z'],
            'jib' => 'M210 56 L306 180 Q266 179 226 174 Q218 114 210 56 Z',
            'stays' => ['M208 44 L312 181', 'M134 92 L208 44', 'M134 92 L99 186'],
            'name' => ['x' => 214, 'y' => 200],
            'bow' => 300,
            'stern' => 108,
        ],
        default => [
            'hull' => 'M96 186 Q210 194 318 180 L300 206 Q205 216 108 206 Z',
            'bootStripe' => 'M104 200 Q205 208 304 198',
            'sheerStripe' => 'M100 190.5 Q210 198.5 314 184.5',
            'cabin' => 'M152 189 L161 178 L226 177 L235 188 Z',
            'windows' => null,
            'masts' => [['x' => 196, 'foot' => 187, 'head' => 36]],
            'booms' => ['M196 167 L124 171'],
            'mainsails' => ['M197 40 Q150 98 125 168 L196 166 Z'],
            'jib' => 'M198 50 L306 180 Q262 180 216 174 Q207 112 198 50 Z',
            'stays' => ['M196 36 L312 181', 'M196 36 L99 186'],
            'name' => ['x' => 212, 'y' => 200],
            'bow' => 300,
            'stern' => 108,
        ],
    };

    // Lattes de la grand-voile du sloop, posées sur la chute (courbe de Bézier de la tête au point d'écoute)
    $battens = [];
    if ($rig === 'sloop') {
        foreach ([0.25, 0.5, 0.75] as $t) {
            $x = (1 - $t) ** 2 * 197 + 2 * (1 - $t) * $t * 150 + $t ** 2 * 125;
            $y = (1 - $t) ** 2 * 40 + 2 * (1 - $t) * $t * 98 + $t ** 2 * 168;
            $battens[] = sprintf('M%.1f %.1f l%.1f -1.5', $x, $y, 14 + 10 * $t);
        }
    }

    // Vagues : une suite de creux et de bosses plus large que le dessin, qui glisse d'une période pour boucler sans à-coup
    $wave = function (float $y, int $period, float $height, bool $closed): string {
        $path = sprintf('M0 %s q%s -%s %s 0', $y, $period / 4, $height, $period / 2);
        $path .= str_repeat(sprintf(' t%s 0', $period / 2), (int) (2 * (400 + $period) / $period) - 1);

        return $closed ? $path.' V260 H0 Z' : $path;
    };

    $hullName = Str::limit($boat->name, 22);
    $id = 'boat-'.$boat->id;

    $facts = array_filter([
        'Année' => $boat->year,
        'Constructeur' => $boat->builder,
        'Loueur' => $boat->renter,
        'Port' => $boat->city,
        'Équipage' => $boat->crew === null ? null : $boat->crew.' '.($boat->crew > 1 ? 'personnes' : 'personne'),
    ], fn ($value) => filled($value));
@endphp

<article {{ $attributes->class(['@container']) }}>
    <div class="grid items-center gap-5 @2xl:grid-cols-[3fr_2fr] @2xl:gap-10">
        <div class="overflow-hidden rounded-3xl ring-1 ring-gray-900/5 ring-inset">
            <svg viewBox="0 0 400 260" aria-hidden="true" focusable="false" class="block h-auto w-full"
                style="--hull: {{ $paint['hull'] }}; --hull-shade: {{ $paint['shade'] }}">
                <defs>
                    <linearGradient id="{{ $id }}-sky" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0" stop-color="currentColor" class="text-primary-100" />
                        <stop offset="1" stop-color="#fff" />
                    </linearGradient>
                    <linearGradient id="{{ $id }}-sea" gradientUnits="userSpaceOnUse" x1="0" y1="194"
                        x2="0" y2="260">
                        <stop offset="0" stop-color="currentColor" class="text-primary-300" />
                        <stop offset="0.45" stop-color="currentColor" class="text-primary-600" />
                        <stop offset="1" stop-color="currentColor" class="text-primary-800" />
                    </linearGradient>
                    <linearGradient id="{{ $id }}-hull" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0" style="stop-color: var(--hull)" />
                        <stop offset="1" style="stop-color: var(--hull-shade)" />
                    </linearGradient>
                    {{-- Voiles : claires au bord de fuite, ombrées le long du mât --}}
                    <linearGradient id="{{ $id }}-sail" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0" stop-color="#fff" />
                        <stop offset="1" stop-color="#dbe3ec" />
                    </linearGradient>
                    <linearGradient id="{{ $id }}-jib" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0" stop-color="#e2e8f0" />
                        <stop offset="1" stop-color="#fff" />
                    </linearGradient>
                </defs>

                {{-- Ciel, soleil voilé, nuages, une mouette --}}
                <rect width="400" height="260" fill="url(#{{ $id }}-sky)" />
                <circle cx="330" cy="56" r="30" fill="#fff" opacity="0.55" />
                <circle cx="330" cy="56" r="17" fill="#fff" opacity="0.9" />
                <g fill="#fff">
                    <ellipse cx="86" cy="62" rx="28" ry="8" />
                    <ellipse cx="104" cy="55" rx="17" ry="10" />
                    <ellipse cx="72" cy="57" rx="13" ry="7" />
                    <ellipse cx="262" cy="96" rx="22" ry="6" opacity="0.8" />
                    <ellipse cx="276" cy="91" rx="12" ry="6" opacity="0.8" />
                </g>
                <path d="M292 120 q5 -5 10 0 q5 -5 10 0" fill="none" stroke="#64748b" stroke-width="1.3"
                    stroke-linecap="round" />

                {{-- Côte au loin --}}
                <path d="M0 195 V183 Q20 172 44 178 Q70 166 96 180 Q114 189 150 195 Z" fill="currentColor"
                    class="text-primary-300" opacity="0.6" />

                {{-- Mer et reflets du soleil --}}
                <rect y="194" width="400" height="66" fill="url(#{{ $id }}-sea)" />
                <path d="M300 199 h16 M326 202 h9 M284 203 h7 M40 204 h10 M176 198 h8" stroke="#fff"
                    stroke-width="1.2" stroke-linecap="round" opacity="0.6" />

                {{-- Le bateau, bercé autour de sa ligne de flottaison --}}
                <g class="origin-[207px_206px] motion-safe:animate-boat-rock"
                    style="animation-delay: -{{ $index * 1.7 }}s">
                    @foreach ($drawing['stays'] as $stay)
                        <path d="{{ $stay }}" stroke="#64748b" stroke-width="0.8" fill="none" />
                    @endforeach

                    @isset($drawing['farHull'])
                        <path d="{{ $drawing['farHull'] }}" style="fill: var(--hull-shade)" />
                    @endisset

                    @foreach ($drawing['mainsails'] as $mainsail)
                        <path d="{{ $mainsail }}" fill="url(#{{ $id }}-sail)" stroke="#cbd5e1"
                            stroke-width="0.6" stroke-linejoin="round" />
                    @endforeach
                    @foreach ($battens as $batten)
                        <path d="{{ $batten }}" stroke="#cbd5e1" stroke-width="0.9" stroke-linecap="round" />
                    @endforeach
                    <path d="{{ $drawing['jib'] }}" fill="url(#{{ $id }}-jib)" stroke="#cbd5e1"
                        stroke-width="0.6" stroke-linejoin="round" />

                    @foreach ($drawing['masts'] as $mast)
                        <path d="M{{ $mast['x'] }} {{ $mast['foot'] }} V{{ $mast['head'] }}" stroke="#475569"
                            stroke-width="2.2" stroke-linecap="round" />
                    @endforeach
                    @foreach ($drawing['booms'] as $boom)
                        <path d="{{ $boom }}" stroke="#475569" stroke-width="2" stroke-linecap="round" />
                    @endforeach

                    {{-- Pavillon en tête du grand mât --}}
                    @php($mainMast = $drawing['masts'][0])
                    <path
                        d="M{{ $mainMast['x'] + 1 }} {{ $mainMast['head'] - 3 }} l17 3.5 l-17 3.5 Z"
                        fill="currentColor"
                        class="text-primary-600 origin-left [transform-box:fill-box] motion-safe:animate-flag-flutter" />

                    <path d="{{ $drawing['cabin'] }}" fill="#f8fafc" stroke="#cbd5e1" stroke-width="0.6" />
                    @if ($drawing['windows'])
                        <path d="{{ $drawing['windows'] }}" fill="#1e293b" opacity="0.85" />
                    @else
                        <g fill="#1e293b" opacity="0.85">
                            <rect x="170" y="180.5" width="12" height="3.5" rx="1.75" />
                            <rect x="188" y="180" width="12" height="3.5" rx="1.75" />
                            <rect x="206" y="179.5" width="12" height="3.5" rx="1.75" />
                        </g>
                    @endif

                    <path d="{{ $drawing['hull'] }}" fill="url(#{{ $id }}-hull)" stroke="#0f172a"
                        stroke-opacity="0.25" stroke-width="0.6" />
                    <g class="{{ $paint['trim'] }}">
                        <path d="{{ $drawing['sheerStripe'] }}" stroke="currentColor" stroke-width="1.2"
                            fill="none" />
                        <path d="{{ $drawing['bootStripe'] }}" stroke="currentColor" stroke-width="2.4"
                            fill="none" />
                        <text x="{{ $drawing['name']['x'] }}" y="{{ $drawing['name']['y'] }}" text-anchor="middle"
                            fill="currentColor" font-size="8.5" font-weight="700" letter-spacing="0.4"
                            @if (mb_strlen($hullName) > 14) textLength="120" lengthAdjust="spacingAndGlyphs" @endif>{{ $hullName }}</text>
                    </g>
                </g>

                {{-- Houle au premier plan : elle couvre le bas de la coque, puis une vague plus lointaine --}}
                <g class="motion-safe:animate-swell">
                    <path d="{{ $wave(206, 80, 3.5, true) }}" fill="url(#{{ $id }}-sea)" opacity="0.94" />
                    <path d="{{ $wave(206, 80, 3.5, false) }}" fill="none" stroke="#fff" stroke-width="1.2"
                        opacity="0.5" />
                </g>
                <ellipse cx="207" cy="214" rx="92" ry="3.5" style="fill: var(--hull)" opacity="0.25" />
                {{-- Écume à l'étrave et sillage à l'arrière --}}
                <path
                    d="M{{ $drawing['bow'] - 16 }} 207 q9 -4.5 19 -2 q7 1.5 14 -0.5 M{{ $drawing['bow'] - 4 }} 210 q8 -2 16 0 M{{ $drawing['stern'] }} 207.5 q-12 -3 -26 0.5 q-9 2 -18 0 M{{ $drawing['stern'] - 6 }} 211 q-10 -1.5 -20 0.5"
                    fill="none" stroke="#fff" stroke-width="1.5" stroke-linecap="round" opacity="0.75" />
                <g class="motion-safe:animate-swell-slow">
                    <path d="{{ $wave(232, 100, 3, false) }}" fill="none" stroke="#fff" stroke-width="1"
                        opacity="0.25" />
                </g>
            </svg>
        </div>

        <div>
            <h3 class="text-xl font-bold break-words text-gray-900">{{ $boat->name }}</h3>
            @if ($boat->type)
                <p class="mt-1 break-words text-gray-600">{{ $boat->type }}</p>
            @endif

            @if ($facts)
                <dl class="mt-4 grid grid-cols-2 gap-x-6 gap-y-3">
                    @foreach ($facts as $label => $value)
                        <div class="min-w-0">
                            <dt class="text-sm text-gray-600">{{ $label }}</dt>
                            <dd class="font-semibold break-words text-gray-900">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            @endif
        </div>
    </div>
</article>
