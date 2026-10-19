<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Event;
use App\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        Article::factory()->create(['title' => 'Article en ligne']);
        Article::factory()->draft()->create(['title' => 'Brouillon secret']);
        Event::factory()->create(['name' => 'Régate de printemps', 'start_time' => now()->addWeek(), 'end_time' => now()->addWeek()]);
        Trip::factory()->create(['end_date' => now()->subDay(), 'start_date' => now()->subDays(3)]);

        $this->get('/')
            ->assertOk()
            ->assertViewIs('homepage.index')
            ->assertSee('Article en ligne')
            ->assertDontSee('Brouillon secret')
            ->assertSee('Régate de printemps');
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
            ->assertDontSee("1 rue du Port\n29200 Brest", false)
            ->assertDontSee('name: "Sortie "pirate""', false)
            ->assertSee('atcb_action', false);
    }

    public function test_legal_page_is_public(): void
    {
        $this->get(route('legal'))->assertOk()->assertViewIs('legal');
    }
}
