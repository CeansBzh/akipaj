<?php

namespace Tests\Feature;

use App\Models\CoursePoint;
use App\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Tests\TestCase;

class TripCourseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Données d'une sortie de trois jours, du vendredi 12 au dimanche 14 juin 2026, avec son parcours.
     *
     * @param  list<array<string, string>>  $course
     * @return array<string, mixed>
     */
    private function tripData(array $course): array
    {
        return [
            'title' => 'Tour de Groix',
            'description' => 'Trois jours en Bretagne sud',
            'start_date' => '2026-06-12',
            'end_date' => '2026-06-14',
            'remove_image' => 'false',
            'course' => $course,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function point(string $name, int $day, float $latitude = 47.5, float $longitude = -3.3): array
    {
        return ['name' => $name, 'day' => (string) $day, 'latitude' => (string) $latitude, 'longitude' => (string) $longitude];
    }

    public function test_a_trip_is_created_with_its_course_ordered_by_day(): void
    {
        $this->actingAsAdmin();

        $this->post(route('trips.store'), $this->tripData([
            $this->point('Lorient', 1, 47.7333, -3.3667),
            $this->point('Belle-Île', 3),
            $this->point('Port-Tudy', 1),
            $this->point('Houat', 2),
        ]))->assertSessionHasNoErrors();

        $course = Trip::where('title', 'Tour de Groix')->firstOrFail()->coursePoints;
        $this->assertSame(
            [['Lorient', 1, 1], ['Port-Tudy', 1, 2], ['Houat', 2, 3], ['Belle-Île', 3, 4]],
            $course->map(fn (CoursePoint $point): array => [$point->name, $point->day, $point->position])->all(),
        );
        $this->assertSame([47.7333, -3.3667], [$course[0]->latitude, $course[0]->longitude]);
    }

    public function test_updating_a_trip_replaces_its_course(): void
    {
        $this->actingAsAdmin();
        $trip = Trip::factory()->create();
        CoursePoint::factory()->for($trip)->count(3)->create();

        $this->put(route('trips.update', $trip), $this->tripData([$this->point('Sauzon', 2)]))
            ->assertSessionHasNoErrors();

        $this->assertSame(['Sauzon'], $trip->coursePoints()->pluck('name')->all());
    }

    public function test_a_form_without_course_keeps_it_and_an_empty_course_removes_it(): void
    {
        $this->actingAsAdmin();
        $trip = Trip::factory()->create();
        CoursePoint::factory()->for($trip)->count(2)->create();
        $dataWithoutCourse = Arr::except($this->tripData([]), 'course');

        $this->put(route('trips.update', $trip), $dataWithoutCourse)->assertSessionHasNoErrors();
        $this->assertSame(2, $trip->coursePoints()->count());

        $this->put(route('trips.update', $trip), [...$dataWithoutCourse, 'course' => ''])->assertSessionHasNoErrors();
        $this->assertSame(0, $trip->coursePoints()->count());
    }

    public function test_course_points_must_be_named_placed_and_within_the_trip_days(): void
    {
        $this->actingAsAdmin();
        $trip = Trip::factory()->create();

        $this->put(route('trips.update', $trip), $this->tripData([
            $this->point('', 1),
            $this->point('Belle-Île', 4),
            $this->point('Ailleurs', 2, 123.0),
        ]))->assertSessionHasErrors([
            'course.0.name' => 'Donnez un nom à chaque point du parcours.',
            'course.1.day' => 'Un point du parcours est rattaché au jour 4, après la fin de la sortie : choisissez un jour entre 1 et 3.',
            'course.2.latitude' => 'Un point du parcours n\'a pas une position valide : replacez-le sur la carte.',
        ], null, 'updateTrip');

        $this->assertSame(0, $trip->coursePoints()->count());
    }

    public function test_trip_page_shows_the_course_day_by_day_with_its_distances(): void
    {
        $this->actingAsMember();
        $trip = Trip::factory()->create(['start_date' => '2026-06-12', 'end_date' => '2026-06-14']);
        // Un degré de latitude vaut 60 milles : 30 milles chaque jour
        foreach ([['Lorient', 1, 47.0], ['Port-Tudy', 1, 47.5], ['Belle-Île', 2, 48.0]] as $index => [$name, $day, $latitude]) {
            CoursePoint::factory()->for($trip)->create([
                'position' => $index + 1, 'name' => $name, 'day' => $day, 'latitude' => $latitude, 'longitude' => -3.0,
            ]);
        }

        $this->get(route('trips.show', $trip))->assertOk()->assertSeeInOrder([
            'Le parcours',
            'Environ 60 milles nautiques en 2 jours',
            'Jour 1', 'vendredi 12 juin', 'Lorient', 'Port-Tudy', 'Environ 30 milles',
            'Jour 2', 'samedi 13 juin', 'Départ de', 'Port-Tudy', 'Belle-Île', 'Environ 30 milles',
        ]);
    }

    public function test_trip_page_has_no_course_without_points(): void
    {
        $this->actingAsMember();
        $trip = Trip::factory()->create();

        $this->get(route('trips.show', $trip))->assertOk()->assertDontSee('Le parcours');
    }
}
