@props(['start', 'end'])

{{-- Dates d'une sortie en toutes lettres : « Le 1er juin 2026 », « Du 12 au 14 juin 2026 », « Du 30 mai au 2 juin 2026 »… --}}

@if ($start->isSameDay($end))
    Le {{ $start->isoFormat('Do MMMM YYYY') }}
@elseif ($start->isSameMonth($end))
    Du {{ $start->isoFormat('Do') }} au {{ $end->isoFormat('Do MMMM YYYY') }}
@elseif ($start->isSameYear($end))
    Du {{ $start->isoFormat('Do MMMM') }} au {{ $end->isoFormat('Do MMMM YYYY') }}
@else
    Du {{ $start->isoFormat('Do MMMM YYYY') }} au {{ $end->isoFormat('Do MMMM YYYY') }}
@endif
