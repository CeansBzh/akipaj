<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Boat;
use App\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TripTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validData(): array
    {
        return [
            'title' => 'Tour de Bretagne',
            'description' => 'Une semaine en mer',
            'start_date' => '2023-07-01',
            'end_date' => '2023-07-08',
            'location' => 'Port de Brest',
            'remove_image' => 'false',
        ];
    }

    public function test_index_groups_trips_by_year(): void
    {
        $this->actingAsMember();
        Trip::factory()->create(['start_date' => '2021-05-01', 'end_date' => '2021-05-02']);
        Trip::factory()->create(['start_date' => '2023-05-01', 'end_date' => '2023-05-02']);
        Trip::factory()->create(['start_date' => '2023-08-01', 'end_date' => '2023-08-02']);

        $tripsByYear = $this->get(route('trips.index'))->assertOk()->viewData('tripsByYear');

        $this->assertEquals([2021, 2023], $tripsByYear->keys()->all());
        $this->assertCount(2, $tripsByYear[2023]);
    }

    /**
     * @param  array<int, int>  $years
     */
    #[DataProvider('yearsWithTrips')]
    public function test_index_opens_the_year_selector_on_the_current_year(array $years, int $initialYear): void
    {
        $this->actingAsMember();
        $this->travelTo('2026-11-08');
        Trip::factory()
            ->count(count($years))
            ->sequence(...array_map(fn (int $year) => ['start_date' => "$year-06-01", 'end_date' => "$year-06-02"], $years))
            ->create();

        $this->get(route('trips.index'))
            ->assertOk()
            ->assertViewHas('initialYear', $initialYear);
    }

    /**
     * @return array<string, array{array<int, int>, int}>
     */
    public static function yearsWithTrips(): array
    {
        return [
            'the current year' => [[2025, 2026, 2027], 2026],
            'the last past year when the current one has no trip' => [[2023, 2024, 2027], 2024],
            'the first year when every trip is upcoming' => [[2027, 2028], 2027],
        ];
    }

    public function test_members_can_see_a_trip(): void
    {
        $this->actingAsMember();
        $trip = Trip::factory()->withBoats(1)->withUsers(2)->withAlbums(1)->create();

        $this->get(route('trips.show', $trip))->assertOk()->assertSee($trip->title);
    }

    public function test_members_cannot_manage_trips(): void
    {
        $this->actingAsMember();
        $trip = Trip::factory()->create();

        $this->get(route('trips.create'))->assertForbidden();
        $this->post(route('trips.store'), $this->validData())->assertForbidden();
        $this->get(route('trips.edit', $trip))->assertForbidden();
        $this->put(route('trips.update', $trip), $this->validData())->assertForbidden();
        $this->delete(route('trips.destroy', $trip))->assertForbidden();
    }

    public function test_admin_can_create_a_trip_with_its_relations(): void
    {
        $this->actingAsAdmin();
        $sailor = $this->createUser();
        $album = Album::factory()->create();
        $this->get(route('trips.create'))->assertOk();

        $this->post(route('trips.store'), array_merge($this->validData(), [
            'users' => [$sailor->id],
            'albums' => [$album->id],
            'boats' => [
                ['name' => 'Pen Duick', 'type' => 'Ketch', 'year' => 1898, 'crew' => 4],
                ['name' => 'Belle Poule'],
            ],
            'image' => UploadedFile::fake()->image('trip.jpg'),
        ]))->assertSessionHasNoErrors()->assertRedirect(route('trips.index'));

        $trip = Trip::where('title', 'Tour de Bretagne')->firstOrFail();
        $this->assertSame('Port de Brest', $trip->location);
        $this->assertTrue($trip->users->contains($sailor));
        $this->assertTrue($trip->albums->contains($album));
        $this->assertEqualsCanonicalizing(['Pen Duick', 'Belle Poule'], $trip->boats->pluck('name')->all());
        Storage::disk('local')->assertExists('public/trips/'.basename((string) $trip->imagePath));
    }

    public function test_trip_creation_is_validated(): void
    {
        $this->actingAsAdmin();

        $this->post(route('trips.store'), [
            'title' => str_repeat('a', 51),
            'start_date' => '2023-07-08',
            'end_date' => '2023-07-01',
            'location' => str_repeat('a', 256),
            'users' => [999],
            'boats' => [['type' => 'Ketch']],
        ])->assertSessionHasErrors(['title', 'description', 'end_date', 'location', 'users.0', 'boats.0.name']);
    }

    public function test_admin_can_update_a_trip_and_its_boats(): void
    {
        $this->actingAsAdmin();
        $trip = Trip::factory()->create(['imagePath' => null]);
        $kept = Boat::factory()->for($trip)->create(['name' => 'Ancien nom']);
        $removed = Boat::factory()->for($trip)->create();
        $sailor = $this->createUser();
        $this->get(route('trips.edit', $trip))->assertOk();

        $this->put(route('trips.update', $trip), array_merge($this->validData(), [
            'users' => [$sailor->id],
            'boats' => [
                ['id' => (string) $kept->id, 'name' => 'Nouveau nom', 'type' => null, 'year' => null, 'builder' => null, 'renter' => null, 'city' => null, 'crew' => 3],
                ['id' => null, 'name' => 'Nouveau bateau', 'type' => null, 'year' => null, 'builder' => null, 'renter' => null, 'city' => null, 'crew' => null],
            ],
        ]))->assertSessionHasNoErrors()->assertRedirect(route('trips.show', $trip));

        $trip->refresh();
        $this->assertSame('Tour de Bretagne', $trip->title);
        $this->assertSame('Port de Brest', $trip->location);
        $this->assertSame([$sailor->id], $trip->users()->pluck('users.id')->all());
        $this->assertSame('Nouveau nom', $kept->refresh()->name);
        $this->assertModelMissing($removed);
        $this->assertEqualsCanonicalizing(['Nouveau nom', 'Nouveau bateau'], $trip->boats()->pluck('name')->all());
    }

    public function test_trip_image_can_be_replaced(): void
    {
        $this->actingAsAdmin();
        Storage::disk('local')->put('public/trips/old.jpg', 'image');
        $trip = Trip::factory()->create(['imagePath' => '/storage/trips/old.jpg']);

        $this->put(route('trips.update', $trip), array_merge($this->validData(), [
            'image' => UploadedFile::fake()->image('new.jpg'),
        ]))->assertSessionHasNoErrors();

        Storage::disk('local')->assertMissing('public/trips/old.jpg');
        Storage::disk('local')->assertExists('public/trips/'.basename((string) $trip->refresh()->imagePath));
    }

    public function test_updating_a_trip_without_boats_removes_them(): void
    {
        $this->actingAsAdmin();
        $trip = Trip::factory()->withBoats(2)->create();

        $this->put(route('trips.update', $trip), $this->validData())->assertSessionHasNoErrors();

        $this->assertSame(0, $trip->boats()->count());
    }

    public function test_admin_can_delete_a_trip(): void
    {
        $this->actingAsAdmin();
        Storage::disk('local')->put('public/trips/old.jpg', 'image');
        $trip = Trip::factory()->withBoats(1)->withUsers(1)->withAlbums(1)->create(['imagePath' => '/storage/trips/old.jpg']);
        $album = $trip->albums->firstOrFail();

        $this->delete(route('trips.destroy', $trip))->assertRedirect(route('trips.index'));

        $this->assertModelMissing($trip);
        // Les albums sont seulement détachés
        $this->assertModelExists($album);
        $this->assertDatabaseCount('boats', 0);
        Storage::disk('local')->assertMissing('public/trips/old.jpg');
    }

    public function test_trip_page_renders_the_description_formatting_without_scripts(): void
    {
        $this->actingAsMember();
        $trip = Trip::factory()->create(['description' => '<div><em>Une semaine</em> en mer<img src="x" onerror="alert(1)"></div>']);

        $this->get(route('trips.show', $trip))
            ->assertOk()
            ->assertSee('<div><em>Une semaine</em> en mer</div>', false)
            ->assertDontSee('onerror', false);
    }

    public function test_trip_page_keeps_headings_and_links_but_drops_scripts(): void
    {
        $this->actingAsMember();
        $trip = Trip::factory()->create([
            'description' => '<h2>Programme</h2><h3>Samedi</h3><h4>Matin</h4><div><strong>Régate</strong> au <a href="https://example.com" onclick="alert(1)">port</a><script>alert(2)</script> <a href="javascript:alert(3)">piège</a></div>',
        ]);

        $this->get(route('trips.show', $trip))
            ->assertOk()
            ->assertSee('<h2>Programme</h2><h3>Samedi</h3><h4>Matin</h4><div><strong>Régate</strong> au <a href="https://example.com">port</a> <a>piège</a></div>', false)
            ->assertDontSee('alert(', false);
    }

    public function test_upcoming_trip_page_shows_the_location_and_offers_to_add_it_to_the_agenda(): void
    {
        $this->actingAsMember();
        $this->travelTo('2026-06-01');
        $trip = Trip::factory()->create(['start_date' => '2026-06-12', 'end_date' => '2026-06-14', 'location' => 'Port de Concarneau']);

        $this->get(route('trips.show', $trip))
            ->assertOk()
            ->assertSee('Port de Concarneau')
            ->assertSee('Ajouter à mon agenda')
            ->assertSee('startDate: "2026-06-12"', false);
    }

    public function test_finished_trip_page_does_not_offer_to_add_it_to_the_agenda(): void
    {
        $this->actingAsMember();
        $this->travelTo('2026-06-15');
        $trip = Trip::factory()->create(['start_date' => '2026-06-12', 'end_date' => '2026-06-14']);

        $this->get(route('trips.show', $trip))
            ->assertOk()
            ->assertDontSee('Ajouter à mon agenda');
    }

    #[DataProvider('oldEventLinks')]
    public function test_old_event_links_lead_to_the_homepage(string $url): void
    {
        $this->actingAsMember();

        $this->get($url)
            ->assertMovedPermanently()
            ->assertRedirect(route('home'));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function oldEventLinks(): array
    {
        return [
            'the programme' => ['/events'],
            'an event' => ['/events/12'],
        ];
    }

    public function test_trip_description_formatting_does_not_count_towards_its_length(): void
    {
        $this->actingAsAdmin();
        $description = '<div><em>'.str_repeat('a', 1000).'</em></div>';

        $this->post(route('trips.store'), array_merge($this->validData(), ['description' => $description]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('trips.index'));

        $this->assertDatabaseHas('trips', ['description' => $description]);
    }

    public function test_trip_description_text_cannot_exceed_1000_characters(): void
    {
        $this->actingAsAdmin();
        $trip = Trip::factory()->create();
        $description = '<div><em>'.str_repeat('a', 1001).'</em></div>';

        $this->put(route('trips.update', $trip), array_merge($this->validData(), ['description' => $description]))
            ->assertSessionHasErrorsIn('updateTrip', ['description' => 'Le texte de description ne peut pas contenir plus de 1000 caractères.']);

        $this->assertDatabaseMissing('trips', ['description' => $description]);
    }
}
