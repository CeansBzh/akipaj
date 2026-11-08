<?php

namespace App\View\Components;

use App\Models\Trip;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

/**
 * Frise des sorties, de la plus récente à la plus ancienne, avec les années et le jour présent.
 *
 * Chaque repère est placé à une distance du précédent proportionnelle au temps qui les sépare : une année occupe
 * YEAR_HEIGHT rem. Deux repères trop proches sont seulement écartés assez pour que leurs libellés ne se chevauchent pas.
 * Les années sans sortie restent sur la frise, pour montrer le temps passé sans sortir.
 */
class TripTimeline extends Component
{
    /**
     * Hauteur d'une année sur la frise, en rem.
     */
    public const YEAR_HEIGHT = 36.0;

    /**
     * Place à laisser sous chaque type de repère pour son libellé, en rem.
     */
    public const LABEL_HEIGHTS = ['year' => 4.0, 'trip' => 5.0, 'today' => 2.5];

    private const SECONDS_PER_YEAR = 365.25 * 24 * 3600;

    /**
     * Repères de la frise, de haut en bas, avec leur hauteur en rem.
     *
     * @var list<array{type: 'year'|'trip'|'today', key: string, top: float, year: int|null, hasTrips: bool, trip: Trip|null}>
     */
    public array $marks = [];

    /**
     * Graduations des débuts de mois, de haut en bas ; celle d'un 1er janvier sépare deux années.
     *
     * @var list<array{top: float, isNewYear: bool}>
     */
    public array $ticks = [];

    /**
     * Hauteur totale de la frise, en rem.
     */
    public float $height = 0.0;

    /**
     * @param  Collection<int, Trip>  $trips
     */
    public function __construct(public Collection $trips)
    {
        if ($trips->isEmpty()) {
            return;
        }

        $today = CarbonImmutable::today();
        $tripYears = $trips->map(fn (Trip $trip): int => $trip->start_date->year)->unique();
        $firstYear = min($tripYears->min(), $today->year);
        $lastYear = max($tripYears->max(), $today->year);

        $moments = [];
        for ($year = $lastYear; $year >= $firstYear; $year--) {
            // Le libellé d'une année est posé en haut de son année, juste avant le 1er janvier suivant
            $moments[] = ['type' => 'year', 'key' => "year-{$year}", 'time' => CarbonImmutable::create($year)->endOfYear()->getTimestamp(),
                'year' => $year, 'hasTrips' => $tripYears->contains($year), 'trip' => null];
        }
        foreach ($trips as $trip) {
            $moments[] = ['type' => 'trip', 'key' => "trip-{$trip->id}", 'time' => $trip->start_date->getTimestamp(),
                'year' => null, 'hasTrips' => false, 'trip' => $trip];
        }
        // À midi, pour qu'une sortie qui commence aujourd'hui passe sous le repère, avec les sorties commencées
        $moments[] = ['type' => 'today', 'key' => 'today', 'time' => $today->midDay()->getTimestamp(),
            'year' => null, 'hasTrips' => false, 'trip' => null];

        usort($moments, fn (array $a, array $b): int => $b['time'] <=> $a['time']);

        $top = 0.0;
        $previous = null;
        foreach ($moments as $moment) {
            if ($previous !== null) {
                $top += $this->distance($previous, $moment['time']);
            }
            $this->marks[] = [...array_diff_key($moment, ['time' => true]), 'top' => round($top, 2)];
            $previous = $moment;
        }

        // La frise s'arrête au 1er janvier de la première année
        $end = CarbonImmutable::create($firstYear)->startOfYear()->getTimestamp();
        $this->height = round($top + $this->distance($previous, $end), 2);

        $anchors = [...array_map(fn (array $moment, array $mark): array => [$moment['time'], $mark['top']], $moments, $this->marks), [$end, $this->height]];
        for ($month = CarbonImmutable::create($lastYear, 12); $month->year >= $firstYear; $month = $month->subMonth()) {
            $this->ticks[] = ['top' => round($this->interpolate($anchors, $month->getTimestamp()), 2), 'isNewYear' => $month->month === 1];
        }
    }

    /**
     * Donne l'écart entre un repère et le moment suivant : proportionnel au temps, mais assez grand pour le libellé du repère.
     *
     * @param  array{type: 'year'|'trip'|'today', time: int}  $from
     */
    private function distance(array $from, int $time): float
    {
        return max(self::LABEL_HEIGHTS[$from['type']], ($from['time'] - $time) / self::SECONDS_PER_YEAR * self::YEAR_HEIGHT);
    }

    /**
     * Place un moment entre les deux repères qui l'entourent, proportionnellement au temps.
     *
     * @param  list<array{int, float}>  $anchors  moments et hauteurs, du plus récent au plus ancien
     */
    private function interpolate(array $anchors, int $time): float
    {
        foreach ($anchors as $index => [$anchorTime, $anchorTop]) {
            if ($anchorTime > $time) {
                continue;
            }
            if ($index === 0) {
                return $anchorTop;
            }
            [$previousTime, $previousTop] = $anchors[$index - 1];

            return $previousTop + ($previousTime - $time) / ($previousTime - $anchorTime) * ($anchorTop - $previousTop);
        }

        return end($anchors)[1];
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
