<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Boat;
use App\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        $this->assertTrue($trip->users->contains($sailor));
        $this->assertTrue($trip->albums->contains($album));
        $this->assertEqualsCanonicalizing(['Pen Duick', 'Belle Poule'], $trip->boats->pluck('name')->all());
        Storage::disk('local')->assertExists('public/trips/' . basename((string) $trip->imagePath));
    }

    public function test_trip_creation_is_validated(): void
    {
        $this->actingAsAdmin();

        $this->post(route('trips.store'), [
            'title' => str_repeat('a', 51),
            'start_date' => '2023-07-08',
            'end_date' => '2023-07-01',
            'users' => [999],
            'boats' => [['type' => 'Ketch']],
        ])->assertSessionHasErrors(['title', 'description', 'end_date', 'users.0', 'boats.0.name']);
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
        Storage::disk('local')->assertExists('public/trips/' . basename((string) $trip->refresh()->imagePath));
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
        // Albums are only detached
        $this->assertModelExists($album);
        $this->assertDatabaseCount('boats', 0);
        Storage::disk('local')->assertMissing('public/trips/old.jpg');
    }
}
