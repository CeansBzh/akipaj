{{-- Repère du jour présent dans la liste des sorties : au-dessus, les sorties à venir ou en cours ; en dessous, celles passées --}}
<div data-timeline-anchor="today" @class(['flex items-center gap-4', $class ?? ''])>
    <span aria-hidden="true" class="bg-primary-200 h-px grow"></span>
    <p class="bg-primary-700 rounded-full px-4 py-1 text-center font-bold text-white">
        Aujourd'hui<span class="font-normal"> · {{ today()->isoFormat('dddd D MMMM YYYY') }}</span>
    </p>
    <span aria-hidden="true" class="bg-primary-200 h-px grow"></span>
</div>
