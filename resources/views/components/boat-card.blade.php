@props(['boat', 'index' => 0])

{{--
    Bateau d'une sortie : son plan au trait sur un papier quadrillé, puis sa fiche.
    Le plan suit le type du bateau (catamaran, ketch, sinon sloop) ; un bateau sur deux est dessiné dans l'autre sens.
    Le plan est décoratif : le nom et les caractéristiques sont écrits dans la fiche.
--}}

@php
    $boatType = Str::lower((string) $boat->type);
    $rig = match (true) {
        Str::contains($boatType, 'cata') => 'catamaran',
        Str::contains($boatType, ['ketch', 'yawl', 'goélette', 'goelette']) => 'ketch',
        default => 'sloop',
    };

    $facts = array_filter(
        [
            'Année' => $boat->year,
            'Constructeur' => $boat->builder,
            'Loueur' => $boat->renter,
            'Port' => $boat->city,
            'Équipage' =>
                $boat->crew === null ? null : $boat->crew . ' ' . ($boat->crew > 1 ? 'personnes' : 'personne'),
        ],
        fn($value) => filled($value),
    );
@endphp

<article {{ $attributes->class(['@container']) }}>
    <div class="@2xl:grid-cols-[minmax(0,22rem)_1fr] @2xl:gap-10 grid items-center gap-5">
        <div class="blueprint aspect-4/5 ring-primary-100 max-h-112 w-full rounded-3xl p-5 ring-1 ring-inset">
            <x-dynamic-component :component="'boat-drawing.' . $rig" :mirrored="$index % 2 === 1" class="text-primary-900 size-full" />
        </div>

        <div>
            <h3 class="wrap-break-word text-xl font-bold text-gray-900">{{ $boat->name }}</h3>
            @if ($boat->type)
                <p class="mt-1 wrap-break-word text-gray-600">{{ $boat->type }}</p>
            @endif

            @if ($facts)
                <dl class="mt-4 grid grid-cols-2 gap-x-6 gap-y-3">
                    @foreach ($facts as $label => $value)
                        <div class="min-w-0">
                            <dt class="text-sm text-gray-600">{{ $label }}</dt>
                            <dd class="wrap-break-word font-semibold text-gray-900">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            @endif
        </div>
    </div>
</article>
