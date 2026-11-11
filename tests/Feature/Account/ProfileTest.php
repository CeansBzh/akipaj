<?php

namespace Tests\Feature\Account;

use App\Enums\UserLevelEnum;
use App\Models\Album;
use App\Models\Photo;
use App\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_members_list_only_shows_validated_members(): void
    {
        $this->actingAsMember();
        $member = $this->createUser(UserLevelEnum::MEMBER, ['name' => 'Henry']);
        $guest = $this->createUser(UserLevelEnum::GUEST, ['name' => 'Lucien']);

        $response = $this->get(route('profile.index'))->assertOk()->assertViewIs('profile.index');

        $members = $response->viewData('members');
        $this->assertTrue($members->contains($member));
        $this->assertFalse($members->contains($guest));
    }

    public function test_members_list_shows_the_current_user_first_then_the_members_with_the_most_trips_done(): void
    {
        $this->travelTo('2026-06-15');
        $currentUser = $this->actingAsLevel(UserLevelEnum::MEMBER, ['name' => 'Michel']);
        $alice = $this->createUser(UserLevelEnum::MEMBER, ['name' => 'Alice']);
        $bruno = $this->createUser(UserLevelEnum::MEMBER, ['name' => 'Bruno']);
        $henry = $this->createUser(UserLevelEnum::MEMBER, ['name' => 'Henry']);
        $this->createUser(UserLevelEnum::MEMBER, ['name' => 'Chloé']);
        $finishedTrip = Trip::factory()->create(['start_date' => '2026-05-01', 'end_date' => '2026-05-03']);
        $ongoingTrip = Trip::factory()->create(['start_date' => '2026-06-14', 'end_date' => '2026-06-16']);
        $upcomingTrip = Trip::factory()->create(['start_date' => '2026-06-16', 'end_date' => '2026-06-18']);
        $alice->trips()->attach([$finishedTrip->id, $ongoingTrip->id]);
        $bruno->trips()->attach([$finishedTrip->id, $upcomingTrip->id]);
        $henry->trips()->attach($ongoingTrip);
        $currentUser->trips()->attach($upcomingTrip);

        $response = $this->get(route('profile.index'))->assertOk();

        $members = $response->viewData('members');
        $this->assertSame(['Michel', 'Alice', 'Bruno', 'Henry', 'Chloé'], $members->pluck('name')->all());
        $this->assertSame([0, 2, 1, 1, 0], $members->pluck('trips_count')->all());
    }

    public function test_members_can_see_any_profile(): void
    {
        $this->actingAsMember();
        $other = $this->createUser(UserLevelEnum::MEMBER, ['name' => 'Henry']);
        $trip = Trip::factory()->create(['start_date' => '2022-06-01', 'end_date' => '2022-06-03']);
        $trip->users()->attach($other);

        $response = $this->get(route('profile.show', 'Henry'))->assertOk()->assertViewIs('profile.show');

        $this->assertTrue($response->viewData('user')->is($other));
        $this->assertEquals([2022], $response->viewData('tripsByYear')->keys()->all());
    }

    public function test_profile_shows_the_trips_done_by_year_and_the_next_trip(): void
    {
        $this->travelTo('2026-06-15');
        $this->actingAsMember();
        $sailor = $this->createUser(UserLevelEnum::MEMBER, ['name' => 'Henry']);
        $lastYearTrip = Trip::factory()->create(['start_date' => '2025-08-10', 'end_date' => '2025-08-12', 'imagePath' => '/storage/trips/ete.jpg']);
        $finishedTrip = Trip::factory()->create(['start_date' => '2026-05-01', 'end_date' => '2026-05-03', 'imagePath' => '/storage/trips/printemps.jpg']);
        $ongoingTrip = Trip::factory()->create(['start_date' => '2026-06-14', 'end_date' => '2026-06-16', 'imagePath' => null]);
        $nextTrip = Trip::factory()->create(['start_date' => '2026-07-01', 'end_date' => '2026-07-03']);
        $laterTrip = Trip::factory()->create(['start_date' => '2026-09-01', 'end_date' => '2026-09-02']);
        Trip::factory()->create(['start_date' => '2026-06-20', 'end_date' => '2026-06-21']);
        $sailor->trips()->attach([$lastYearTrip->id, $finishedTrip->id, $ongoingTrip->id, $nextTrip->id, $laterTrip->id]);

        $response = $this->get(route('profile.show', 'Henry'))->assertOk();

        $tripsByYear = $response->viewData('tripsByYear');
        $this->assertSame([2026, 2025], $tripsByYear->keys()->all());
        $this->assertSame([$ongoingTrip->id, $finishedTrip->id], $tripsByYear[2026]->pluck('id')->all());
        $this->assertSame([$lastYearTrip->id], $tripsByYear[2025]->pluck('id')->all());
        // 3 jours en août, 3 jours en mai, et les 2 premiers jours de la sortie en cours
        $this->assertSame(8, $response->viewData('daysAboard'));
        $this->assertSame('/storage/trips/printemps.jpg', $response->viewData('coverImage'));
        $this->assertTrue($response->viewData('nextTrip')->is($nextTrip));
    }

    public function test_profile_shows_the_six_latest_photos_and_links_to_the_gallery_filtered_on_the_user(): void
    {
        $this->actingAsMember();
        $sailor = $this->createUser(UserLevelEnum::MEMBER, ['name' => 'Henry']);
        $album = Album::factory()->create();
        $photos = collect(range(1, 7))->map(
            fn (int $month): Photo => Photo::factory()->for($album)->for($sailor)->create(['taken_at' => "2025-0{$month}-01"]),
        );
        Photo::factory()->for($album)->for($this->createUser())->create(['taken_at' => '2025-12-01']);

        $response = $this->get(route('profile.show', 'Henry'))->assertOk();

        $this->assertSame(
            [$photos[6]->id, $photos[5]->id, $photos[4]->id, $photos[3]->id, $photos[2]->id, $photos[1]->id],
            $response->viewData('latestPhotos')->pluck('id')->all(),
        );
        $response->assertSee(route('photos.index', ['auteurs' => [$sailor->id]]));
    }

    public function test_only_the_owner_sees_the_link_to_edit_the_profile(): void
    {
        $this->actingAsMember()->forceFill(['name' => 'Michel'])->save();
        $this->createUser(UserLevelEnum::MEMBER, ['name' => 'Henry']);

        $this->get(route('profile.show', 'Michel'))->assertOk()->assertSee(route('settings.edit'));
        $this->get(route('profile.show', 'Henry'))->assertOk()->assertDontSee(route('settings.edit'));
    }

    public function test_guest_level_users_can_see_their_own_profile(): void
    {
        $user = $this->actingAsLevel(UserLevelEnum::GUEST, ['name' => 'Lucien']);

        $response = $this->get(route('profile.show', 'Lucien'))->assertOk();

        $this->assertTrue($response->viewData('user')->is($user));
    }

    public function test_guest_level_users_are_redirected_from_other_profiles(): void
    {
        $this->actingAsLevel(UserLevelEnum::GUEST, ['name' => 'Lucien']);
        $this->createUser(UserLevelEnum::MEMBER, ['name' => 'Henry']);

        $this->get(route('profile.show', 'Henry'))->assertRedirect(route('profile.show', 'Lucien'));
    }

    public function test_profile_without_username_redirects_to_own_profile(): void
    {
        $this->actingAsMember()->forceFill(['name' => 'Henry'])->save();

        $this->get(route('profile.show'))->assertRedirect(route('profile.show', 'Henry'));
    }

    public function test_unknown_username_returns_404(): void
    {
        $this->actingAsMember();

        $this->get(route('profile.show', 'nobody'))->assertNotFound();
    }

    public function test_visitors_cannot_see_profiles(): void
    {
        $this->createUser(UserLevelEnum::MEMBER, ['name' => 'Henry']);

        $this->get(route('profile.show', 'Henry'))->assertNotFound();
        $this->get(route('profile.show'))->assertNotFound();
    }
}
