<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HomepageTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitors_see_the_public_landing_page(): void
    {
        $this->get('/')->assertOk()->assertViewIs('index');
    }

    public function test_authenticated_users_see_the_members_homepage(): void
    {
        $this->actingAsMember();
        Event::factory()->create(['name' => 'Régate de printemps', 'start_time' => now()->addWeek(), 'end_time' => now()->addWeek()]);
        Trip::factory()->create(['end_date' => now()->subDay(), 'start_date' => now()->subDays(3)]);

        $this->get('/')
            ->assertOk()
            ->assertViewIs('homepage.index')
            ->assertSee('Régate de printemps');
    }

    public function test_agenda_reminders_show_the_event_description_without_formatting(): void
    {
        $this->actingAsMember();
        Event::factory()->create([
            'description' => '<div>Rendez-vous au <strong>port</strong></div><div>Retour le soir</div>',
            'start_time' => now()->addWeek(),
            'end_time' => now()->addWeek(),
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Rendez-vous au port Retour le soir.[br]Voir sur le site', false);
    }

    public function test_add_to_agenda_script_encodes_event_values(): void
    {
        $this->actingAsMember();
        Event::factory()->create([
            'name' => 'Sortie "pirate"',
            'location' => "1 rue du Port\n29200 Brest",
            'start_time' => now()->addWeek(),
            'end_time' => now()->addWeek(),
        ]);

        // A raw line break or quote inside the JS string literal would be a syntax error in the browser
        $this->get('/')
            ->assertOk()
            ->assertSee('location: \'1 rue du Port\n29200 Brest\'', false)
            ->assertDontSee('name: "Sortie "pirate""', false)
            ->assertSee('addToAgenda', false);
    }

    #[DataProvider('eventStatusDays')]
    public function test_event_tickets_show_whether_the_event_is_ongoing(string $now, string $status): void
    {
        $this->actingAsMember();
        $this->travelTo($now);
        Event::factory()->create(['start_time' => '2026-06-12', 'end_time' => '2026-06-14']);

        $this->get('/')
            ->assertOk()
            ->assertSee($status);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function eventStatusDays(): array
    {
        return [
            'the day before' => ['2026-06-11 18:00', 'Demain'],
            'the first day' => ['2026-06-12 08:00', 'En cours'],
            'the evening of the last day' => ['2026-06-14 23:00', 'En cours'],
        ];
    }

    public function test_finished_events_leave_the_homepage(): void
    {
        $this->actingAsMember();
        $this->travelTo('2026-06-15 08:00');
        Event::factory()->create(['name' => 'Régate terminée', 'start_time' => '2026-06-12', 'end_time' => '2026-06-14']);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Régate terminée');
    }

    public function test_event_tickets_show_the_departure_and_return_days_and_the_location(): void
    {
        $this->actingAsMember();
        $this->travelTo('2026-06-01');
        Event::factory()->create(['start_time' => '2026-06-12', 'end_time' => '2026-06-14', 'location' => 'Port de Concarneau']);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['Départ', 'vendredi 12 juin 2026', 'Retour', 'dimanche 14 juin 2026', 'Lieu', 'Port de Concarneau']);
    }

    public function test_single_day_event_tickets_show_one_date(): void
    {
        $this->actingAsMember();
        $this->travelTo('2026-05-20');
        Event::factory()->create(['start_time' => '2026-06-01', 'end_time' => '2026-06-01']);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['Date', 'lundi 1er juin 2026'])
            ->assertDontSee('Retour</dt>', false);
    }

    public function test_only_the_closest_event_gets_the_ticket(): void
    {
        $this->actingAsMember();
        $this->travelTo('2026-06-01');
        Event::factory()->create(['start_time' => '2026-06-20', 'end_time' => '2026-06-20']);
        Event::factory()->create(['start_time' => '2026-06-12', 'end_time' => '2026-06-12']);

        // Le billet écrit le jour de la semaine, les petites cartes non
        $this->get('/')
            ->assertOk()
            ->assertSee('vendredi 12 juin 2026')
            ->assertSee('Le 20 juin 2026')
            ->assertDontSee('samedi 20 juin 2026');
    }

    public function test_the_programme_lists_every_upcoming_event(): void
    {
        $this->actingAsMember();
        Event::factory()->count(5)->create(['start_time' => now()->addWeek(), 'end_time' => now()->addWeek()]);
        Event::factory()->create(['name' => 'Convoyage de fin de saison', 'start_time' => now()->addMonth(), 'end_time' => now()->addMonth()]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Convoyage de fin de saison');
    }

    #[DataProvider('tripDateRanges')]
    public function test_last_trip_cards_write_their_dates_in_full(string $start, string $end, string $dates): void
    {
        $this->actingAsMember();
        $this->travelTo('2026-11-01');
        Trip::factory()->create(['start_date' => $start, 'end_date' => $end]);

        $this->get('/')
            ->assertOk()
            ->assertSee($dates);
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function tripDateRanges(): array
    {
        return [
            'a single day' => ['2026-06-01', '2026-06-01', 'Le 1er juin 2026'],
            'days of the same month' => ['2026-06-12', '2026-06-14', 'Du 12 au 14 juin 2026'],
            'days of two months' => ['2026-05-30', '2026-06-02', 'Du 30 mai au 2 juin 2026'],
            'days of two years' => ['2025-12-30', '2026-01-02', 'Du 30 décembre 2025 au 2 janvier 2026'],
        ];
    }

    public function test_last_trip_cards_show_the_description_without_formatting(): void
    {
        $this->actingAsMember();
        Trip::factory()->create([
            'description' => '<div>Mouillage aux <strong>Glénan</strong></div>',
            'start_date' => now()->subDays(3),
            'end_date' => now()->subDay(),
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Mouillage aux Glénan');
    }

    public function test_legal_page_is_public(): void
    {
        $this->get(route('legal'))->assertOk()->assertViewIs('legal');
    }
}
