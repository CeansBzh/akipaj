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

    $facts = array_filter([
        'Année' => $boat->year,
        'Constructeur' => $boat->builder,
        'Loueur' => $boat->renter,
        'Port' => $boat->city,
        'Équipage' => $boat->crew === null ? null : $boat->crew.' '.($boat->crew > 1 ? 'personnes' : 'personne'),
    ], fn ($value) => filled($value));
@endphp

<article {{ $attributes->class(['@container']) }}>
    <div class="grid items-center gap-5 @2xl:grid-cols-[minmax(0,22rem)_1fr] @2xl:gap-10">
        <div class="blueprint aspect-4/5 max-h-[28rem] w-full rounded-3xl p-5 ring-1 ring-primary-100 ring-inset">
            <x-dynamic-component :component="'boat-drawing.'.$rig" :mirrored="$index % 2 === 1"
                class="size-full text-primary-900" />
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
