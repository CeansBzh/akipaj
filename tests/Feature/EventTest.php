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
            'description' => str_repeat('a', 141),
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
}
