<?php

namespace App\View\Components;

use App\Models\Trip;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

/**
 * Frise des sorties : toutes les années, de la plus récente à la plus ancienne, années sans sortie comprises.
 *
 * Seule l'année en cours de lecture est dépliée sur ses sorties et, s'il tombe dedans, sur le jour présent.
 * Entre deux repères d'une année, un écart compte les jours qui les séparent : la vue lui donne une hauteur
 * à proportion, ajoutée à celle des libellés, qui ne se chevauchent donc jamais.
 */
class TripTimeline extends Component
{
    private const SECONDS_PER_DAY = 86400;

    /**
     * Hauteur totale, en rem, des écarts d'une année dépliée, en plus de ses libellés.
     */
    private const YEAR_GAPS_HEIGHT_REM = 6;

    /**
     * Années de la frise, de la plus récente à la plus ancienne. Leurs repères vont du 31 décembre au 1er janvier,
     * chacun précédé de l'écart qui le sépare du précédent ; un dernier écart va jusqu'au 1er janvier.
     *
     * @var list<array{year: int, tripCount: int, hasToday: bool, entries: list<array{type: 'gap'|'trip'|'today', days: float, trip: Trip|null}>}>
     */
    public array $years = [];

    /**
     * @param  Collection<int, Trip>  $trips
     */
    public function __construct(public Collection $trips)
    {
        if ($trips->isEmpty()) {
            return;
        }

        $today = CarbonImmutable::today();
        $tripsByYear = $trips->groupBy(fn (Trip $trip): int => $trip->start_date->year);
        $firstYear = min($tripsByYear->keys()->min(), $today->year);
        $lastYear = max($tripsByYear->keys()->max(), $today->year);

        for ($year = $lastYear; $year >= $firstYear; $year--) {
            /** @var Collection<int, Trip> $yearTrips */
            $yearTrips = $tripsByYear->get($year, new Collection);

            $moments = $yearTrips
                ->map(fn (Trip $trip): array => ['type' => 'trip', 'time' => $trip->start_date->getTimestamp(), 'trip' => $trip])
                ->values()
                ->all();
            if ($today->year === $year) {
                // À midi, pour qu'une sortie qui commence aujourd'hui passe sous le repère, avec les sorties commencées
                $moments[] = ['type' => 'today', 'time' => $today->midDay()->getTimestamp(), 'trip' => null];
            }
            usort($moments, fn (array $a, array $b): int => $b['time'] <=> $a['time']);

            $entries = [];
            $previousTime = CarbonImmutable::create($year)->endOfYear()->getTimestamp();
            foreach ($moments as $moment) {
                $entries[] = $this->gap($previousTime, $moment['time']);
                $entries[] = ['type' => $moment['type'], 'days' => 0.0, 'trip' => $moment['trip']];
                $previousTime = $moment['time'];
            }
            $entries[] = $this->gap($previousTime, CarbonImmutable::create($year)->startOfYear()->getTimestamp());

            $this->years[] = ['year' => $year, 'tripCount' => $yearTrips->count(), 'hasToday' => $today->year === $year, 'entries' => $entries];
        }
    }

    /**
     * Donne l'écart entre deux moments d'une année, en jours.
     *
     * @return array{type: 'gap', days: float, trip: null}
     */
    private function gap(int $from, int $to): array
    {
        return ['type' => 'gap', 'days' => round(max(0, $from - $to) / self::SECONDS_PER_DAY, 1), 'trip' => null];
    }

    /**
     * Donne la hauteur CSS d'un écart de la frise, à proportion des jours qu'il compte.
     */
    public function gapHeight(float $days): string
    {
        return round($days / 365 * self::YEAR_GAPS_HEIGHT_REM, 3).'rem';
    }

    /**
     * Donne les dates courtes d'une sortie, sans l'année que la frise montre déjà : « 12 juin », « 12 – 14 juin », « 30 mai – 2 juin ».
     */
    public function shortDates(Trip $trip): string
    {
        [$start, $end] = [$trip->start_date, $trip->end_date];

        return match (true) {
            $start->isSameDay($end) => $start->isoFormat('Do MMMM'),
            $start->isSameMonth($end) => $start->isoFormat('Do').' – '.$end->isoFormat('Do MMMM'),
            default => $start->isoFormat('Do MMMM').' – '.$end->isoFormat('Do MMMM'),
        };
    }

    /**
     * Donne la vue de la frise.
     */
    public function render(): View|Closure|string
    {
        return view('components.trip-timeline');
    }
}
