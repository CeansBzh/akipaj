<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventTest extends TestCase
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
            'name' => 'Régate',
            'description' => 'La régate annuelle',
            'start_time' => now()->addWeek()->format('Y-m-d H:i'),
            'end_time' => now()->addWeek()->addDay()->format('Y-m-d H:i'),
            'location' => 'Brest',
        ];
    }

    public function test_index_only_lists_upcoming_events_in_chronological_order(): void
    {
        $this->actingAsMember();
        $later = Event::factory()->create(['start_time' => now()->addMonth(), 'end_time' => now()->addMonth()]);
        $sooner = Event::factory()->create(['start_time' => now()->addDay(), 'end_time' => now()->addDay()]);
        $passed = Event::factory()->passed()->create();

        $events = $this->get(route('events.index'))->assertOk()->viewData('events');

        $this->assertSame([$sooner->id, $later->id], $events->pluck('id')->all());
        $this->assertFalse($events->contains($passed));
    }

    public function test_members_can_see_an_event(): void
    {
        $this->actingAsMember();
        $event = Event::factory()->create();

        $this->get(route('events.show', $event))->assertOk()->assertSee($event->name);
    }

    public function test_members_cannot_manage_events(): void
    {
        $this->actingAsMember();
        $event = Event::factory()->create();

        $this->get(route('events.create'))->assertForbidden();
        $this->post(route('events.store'), $this->validData())->assertForbidden();
        $this->get(route('events.edit', $event))->assertForbidden();
        $this->put(route('events.update', $event), $this->validData())->assertForbidden();
        $this->delete(route('events.destroy', $event))->assertForbidden();
    }

    public function test_admin_can_create_an_event(): void
    {
        $this->actingAsAdmin();
        $this->get(route('events.create'))->assertOk();

        $this->post(route('events.store'), $this->validData() + ['image' => UploadedFile::fake()->image('event.jpg')])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('events.index'));

        $event = Event::where('name', 'Régate')->firstOrFail();
        $this->assertSame('Brest', $event->location);
        Storage::disk('local')->assertExists('public/events/'.basename((string) $event->imagePath));
    }

    public function test_events_cannot_start_in_the_past_or_end_before_starting(): void
    {
        $this->actingAsAdmin();

        $this->post(route('events.store'), array_merge($this->validData(), [
            'start_time' => now()->subDays(2)->format('Y-m-d'),
            'end_time' => now()->subDays(3)->format('Y-m-d'),
            'name' => str_repeat('a', 51),
            'description' => str_repeat('a', 1001),
        ]))->assertSessionHasErrors(['start_time', 'end_time', 'name', 'description']);
    }

    public function test_admin_can_update_an_event(): void
    {
        $this->actingAsAdmin();
        Storage::disk('local')->put('public/events/old.jpg', 'image');
        $event = Event::factory()->create(['imagePath' => '/storage/events/old.jpg']);
        $this->get(route('events.edit', $event))->assertOk();

        $this->put(route('events.update', $event), array_merge($this->validData(), [
            'name' => 'Nouveau nom',
            'remove_image' => 'true',
        ]))->assertSessionHasNoErrors()->assertRedirect(route('events.show', $event));

        $event->refresh();
        $this->assertSame('Nouveau nom', $event->name);
        $this->assertNull($event->imagePath);
        Storage::disk('local')->assertMissing('public/events/old.jpg');
    }

    public function test_admin_can_delete_an_event_and_its_image(): void
    {
        $this->actingAsAdmin();
        Storage::disk('local')->put('public/events/old.jpg', 'image');
        $event = Event::factory()->create(['imagePath' => '/storage/events/old.jpg']);

        $this->delete(route('events.destroy', $event))->assertRedirect(route('events.index'));

        $this->assertModelMissing($event);
        Storage::disk('local')->assertMissing('public/events/old.jpg');
    }

    public function test_event_page_renders_the_description_formatting_without_scripts(): void
    {
        $this->actingAsMember();
        $event = Event::factory()->create([
            'description' => '<h2>Programme</h2><h3>Samedi</h3><h4>Matin</h4><div><strong>Régate</strong> au <a href="https://example.com" onclick="alert(1)">port</a><script>alert(2)</script> <a href="javascript:alert(3)">piège</a></div>',
        ]);

        $this->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('<h2>Programme</h2><h3>Samedi</h3><h4>Matin</h4><div><strong>Régate</strong> au <a href="https://example.com">port</a> <a>piège</a></div>', false)
            ->assertDontSee('alert(', false);
    }

    public function test_event_cards_and_reminders_show_the_description_without_formatting(): void
    {
        $this->actingAsMember();
        Event::factory()->create(['description' => '<h2>Programme</h2><div>Rendez-vous au <strong>port</strong></div><div>Retour le soir</div>']);

        // La carte vient d'abord, puis le script du bouton « Ajouter à l'agenda »
        $this->get(route('events.index'))
            ->assertOk()
            ->assertSeeInOrder(['Programme Rendez-vous au port Retour le soir', 'Programme Rendez-vous au port Retour le soir.[br]Voir sur le site'], false);
    }

    public function test_event_description_formatting_does_not_count_towards_its_length(): void
    {
        $this->actingAsAdmin();
        $description = '<div><strong>'.str_repeat('a', 1000).'</strong></div>';

        $this->post(route('events.store'), array_merge($this->validData(), ['description' => $description]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('events.index'));

        $this->assertDatabaseHas('events', ['description' => $description]);
    }

    public function test_event_description_text_cannot_exceed_1000_characters(): void
    {
        $this->actingAsAdmin();

        $this->post(route('events.store'), array_merge($this->validData(), [
            'description' => '<div><strong>'.str_repeat('a', 1001).'</strong></div>',
        ]))->assertSessionHasErrors(['description' => 'Le texte de description ne peut pas contenir plus de 1000 caractères.']);

        $this->assertDatabaseCount('events', 0);
    }

    public function test_event_description_needs_text_besides_the_formatting(): void
    {
        $this->actingAsAdmin();

        $this->post(route('events.store'), array_merge($this->validData(), ['description' => '<div><br></div>']))
            ->assertSessionHasErrors(['description' => 'Le champ description est obligatoire.']);

        $this->assertDatabaseCount('events', 0);
    }
}
