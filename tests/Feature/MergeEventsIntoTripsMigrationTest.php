<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Fusion des événements dans les sorties : la migration est rejouée sur des événements d'avant la fusion.
 */
class MergeEventsIntoTripsMigrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    /**
     * Annule la migration pour retrouver la table des événements, y enregistre ceux donnés, puis rejoue la migration.
     *
     * @param  array<int, array<string, mixed>>  $events
     */
    private function replayMergeMigrationWith(array $events): void
    {
        $migration = require database_path('migrations/2026_11_08_095928_merge_events_into_trips.php');
        $migration->down();
        DB::table('events')->insert($events);

        $migration->up();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function event(array $attributes): array
    {
        return array_merge([
            'name' => 'Régate',
            'description' => '<div>La régate annuelle</div>',
            'location' => null,
            'imagePath' => null,
            'created_at' => '2026-05-01 10:00:00',
            'updated_at' => '2026-05-01 10:00:00',
        ], $attributes);
    }

    public function test_upcoming_and_ongoing_events_become_trips_with_their_image(): void
    {
        $this->travelTo('2026-06-13 18:00');
        Storage::put('public/events/1780000000.jpg', 'image');

        $this->replayMergeMigrationWith([
            $this->event([
                'name' => 'Régate en cours',
                'location' => 'Port de Brest',
                'start_time' => '2026-06-12 00:00:00',
                'end_time' => '2026-06-13 00:00:00',
                'imagePath' => '/storage/events/1780000000.jpg',
            ]),
            $this->event(['name' => 'Tour de Groix', 'start_time' => '2026-07-01 00:00:00', 'end_time' => '2026-07-03 00:00:00']),
        ]);

        $this->assertDatabaseHas('trips', [
            'title' => 'Régate en cours',
            'description' => '<div>La régate annuelle</div>',
            'location' => 'Port de Brest',
            'start_date' => '2026-06-12',
            'end_date' => '2026-06-13',
            'imagePath' => '/storage/trips/event-1780000000.jpg',
            'created_at' => '2026-05-01 10:00:00',
        ]);
        $this->assertDatabaseHas('trips', ['title' => 'Tour de Groix', 'start_date' => '2026-07-01', 'end_date' => '2026-07-03', 'imagePath' => null]);
        Storage::assertMissing('public/events/1780000000.jpg');
        Storage::assertExists('public/trips/event-1780000000.jpg');
        $this->assertFalse(Schema::hasTable('events'));
    }

    public function test_past_events_are_dropped_with_their_image(): void
    {
        $this->travelTo('2026-06-13 08:00');
        Storage::put('public/events/1770000000.jpg', 'image');

        $this->replayMergeMigrationWith([
            $this->event([
                'start_time' => '2026-06-10 00:00:00',
                'end_time' => '2026-06-12 00:00:00',
                'imagePath' => '/storage/events/1770000000.jpg',
            ]),
        ]);

        $this->assertDatabaseCount('trips', 0);
        Storage::assertMissing('public/events/1770000000.jpg');
    }
}
