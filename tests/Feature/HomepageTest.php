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

    public function test_legal_page_is_public(): void
    {
        $this->get(route('legal'))->assertOk()->assertViewIs('legal');
    }
}
