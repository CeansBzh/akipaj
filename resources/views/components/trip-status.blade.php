@props(['trip'])

@if ($trip->isOngoing())
    En cours
@else
    {{ ucfirst(
        $trip->start_date->diffForHumans(today(), [
            'syntax' => Carbon\CarbonInterface::DIFF_RELATIVE_TO_NOW,
            'options' => Carbon\CarbonInterface::ONE_DAY_WORDS | Carbon\CarbonInterface::TWO_DAY_WORDS,
        ]),
    ) }}
@endif
