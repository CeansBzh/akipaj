@props(['waves' => true])

{{--
    Voilier vu de profil, au trait, cap à droite : coque, rouf, mât, grand-voile, foc et flamme en tête de mât.
    Le dessin prend la couleur du texte ; les voiles et la coque en sont teintées. Les traits gardent la même épaisseur
    quelle que soit la taille du dessin. Le dessin est décoratif.
--}}

<svg viewBox="0 0 160 128" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2"
    stroke-linecap="round" stroke-linejoin="round" {{ $attributes }}>
    <path vector-effect="non-scaling-stroke" d="M74 10v84" />
    <path vector-effect="non-scaling-stroke" fill="currentColor" fill-opacity=".12" d="M74 14v72H30q12-38 44-72Z" />
    <path vector-effect="non-scaling-stroke" stroke-width="1" stroke-opacity=".6"
        d="m37 67 37-2m-27-16 27-1.5M59 31l15-1" />
    <path vector-effect="non-scaling-stroke" stroke-width="2.5" d="M75 86H28" />
    <path vector-effect="non-scaling-stroke" fill="currentColor" fill-opacity=".12" d="m78 17 60 66-52 5q4-36-8-71Z" />
    <path vector-effect="non-scaling-stroke" stroke-width="1" d="m74 12 72 80" />
    <path vector-effect="non-scaling-stroke" fill="currentColor" d="m74 10 12 3-12 3" />
    <path vector-effect="non-scaling-stroke" d="m50 94.5 6-5.5h28l4 5.5" />
    <path vector-effect="non-scaling-stroke" stroke-width="2.5" fill="currentColor" fill-opacity=".12"
        d="M20 94q64 4 128-4-6 16-26 22-10 3-18 3H44q-12 0-17-6-5-6-7-15Z" />
    <path vector-effect="non-scaling-stroke" stroke-width="1" d="M29 102q55 4 112-4" />
    @if ($waves)
        <path vector-effect="non-scaling-stroke" d="M10 120q7-4 14 0t14 0 14 0 14 0 14 0 14 0 14 0 14 0 14 0 14 0" />
        <path vector-effect="non-scaling-stroke" stroke-opacity=".5" d="M40 126q7-3 14 0t14 0 14 0 14 0 14 0" />
    @endif
</svg>
