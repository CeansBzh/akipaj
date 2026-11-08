<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\View\Components\TripTimeline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripTimelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_marks_go_from_the_newest_year_to_the_oldest_with_empty_years_and_today(): void
    {
        $this->travelTo('2024-02-01');
        $summer = Trip::factory()->create(['start_date' => '2023-08-01', 'end_date' => '2023-08-03']);
        $spring = Trip::factory()->create(['start_date' => '2021-05-01', 'end_date' => '2021-05-02']);

        $timeline = new TripTimeline(Trip::orderByDesc('start_date')->get());

        $this->assertSame(
            ['year-2024', 'today', 'year-2023', "trip-{$summer->id}", 'year-2022', 'year-2021', "trip-{$spring->id}"],
            array_column($timeline->marks, 'key'),
        );
        $this->assertSame([false, true, false, true], array_column(array_filter($timeline->marks, fn (array $mark): bool => $mark['type'] === 'year'), 'hasTrips'));
        $this->assertCount(4 * 12, $timeline->ticks);
    }

    public function test_marks_are_spaced_by_the_time_between_them_but_never_closer_than_a_label(): void
    {
        $september = Trip::factory()->create(['start_date' => '2022-09-01', 'end_date' => '2022-09-02']);
        $march = Trip::factory()->create(['start_date' => '2022-03-02', 'end_date' => '2022-03-03']);
        $dayBefore = Trip::factory()->create(['start_date' => '2022-03-01', 'end_date' => '2022-03-01']);
        $this->travelTo('2022-12-31');

        $tops = array_column((new TripTimeline(Trip::orderByDesc('start_date')->get()))->marks, 'top', 'key');

        $this->assertEqualsWithDelta(TripTimeline::YEAR_HEIGHT / 2, $tops["trip-{$march->id}"] - $tops["trip-{$september->id}"], 0.2);
        $this->assertSame(TripTimeline::LABEL_HEIGHTS['trip'], $tops["trip-{$dayBefore->id}"] - $tops["trip-{$march->id}"]);
    }

    public function test_short_dates_leave_out_the_year(): void
    {
        $timeline = new TripTimeline(collect());

        $this->assertSame('1er juin', $timeline->shortDates(Trip::factory()->make(['start_date' => '2026-06-01', 'end_date' => '2026-06-01'])));
        $this->assertSame('12 – 14 juin', $timeline->shortDates(Trip::factory()->make(['start_date' => '2026-06-12', 'end_date' => '2026-06-14'])));
        $this->assertSame('30 mai – 2 juin', $timeline->shortDates(Trip::factory()->make(['start_date' => '2026-05-30', 'end_date' => '2026-06-02'])));
    }
}
