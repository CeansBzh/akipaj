@props(['event'])

{{-- Statut d'un événement : « En cours », ou dans combien de temps il commence (« Demain », « Dans 3 jours »…) --}}

@if ($event->isOngoing())
    En cours
@else
    {{ ucfirst(
        $event->start_time->diffForHumans(today(), [
            'syntax' => Carbon\CarbonInterface::DIFF_RELATIVE_TO_NOW,
            'options' => Carbon\CarbonInterface::ONE_DAY_WORDS | Carbon\CarbonInterface::TWO_DAY_WORDS,
        ]),
    ) }}
@endif
