<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\View\Components\TripTimeline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripTimelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_years_go_from_the_newest_to_the_oldest_with_empty_years_and_today(): void
    {
        $this->travelTo('2024-02-01');
        Trip::factory()->create(['start_date' => '2023-08-01', 'end_date' => '2023-08-03']);
        Trip::factory()->create(['start_date' => '2021-05-01', 'end_date' => '2021-05-02']);

        $years = (new TripTimeline(Trip::orderByDesc('start_date')->get()))->years;

        $this->assertSame([2024, 2023, 2022, 2021], array_column($years, 'year'));
        $this->assertSame([0, 1, 0, 1], array_column($years, 'tripCount'));
        $this->assertSame([true, false, false, false], array_column($years, 'hasToday'));
    }

    public function test_the_marks_of_a_year_are_separated_by_the_days_between_them(): void
    {
        $this->travelTo('2022-06-15');
        $september = Trip::factory()->create(['start_date' => '2022-09-01', 'end_date' => '2022-09-02']);
        $march = Trip::factory()->create(['start_date' => '2022-03-02', 'end_date' => '2022-03-03']);

        $entries = (new TripTimeline(Trip::orderByDesc('start_date')->get()))->years[0]['entries'];

        $this->assertSame(['gap', 'trip', 'gap', 'today', 'gap', 'trip', 'gap'], array_column($entries, 'type'));
        $this->assertTrue($entries[1]['trip']->is($september));
        $this->assertTrue($entries[5]['trip']->is($march));
        // Du 31 décembre au 1er septembre, au 15 juin à midi, au 2 mars, puis au 1er janvier
        $this->assertEqualsWithDelta([122, 77.5, 105.5, 60], array_column(array_filter($entries, fn (array $entry): bool => $entry['type'] === 'gap'), 'days'), 0.1);
    }

    public function test_short_dates_leave_out_the_year(): void
    {
        $timeline = new TripTimeline(collect());

        $this->assertSame('1er juin', $timeline->shortDates(Trip::factory()->make(['start_date' => '2026-06-01', 'end_date' => '2026-06-01'])));
        $this->assertSame('12 – 14 juin', $timeline->shortDates(Trip::factory()->make(['start_date' => '2026-06-12', 'end_date' => '2026-06-14'])));
        $this->assertSame('30 mai – 2 juin', $timeline->shortDates(Trip::factory()->make(['start_date' => '2026-05-30', 'end_date' => '2026-06-02'])));
    }
}
