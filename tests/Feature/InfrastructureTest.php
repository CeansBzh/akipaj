<?php

namespace Tests\Feature;

use App\Enums\AlertLevelEnum;
use App\Enums\UserLevelEnum;
use App\Http\Middleware\EnsureHasLevel;
use App\Models\Photo;
use App\View\Components\Alert;
use Database\Seeders\LocalEnvironmentSeeder;
use Illuminate\Console\Scheduling\Event as ScheduledEvent;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoApiTransport;
use Tests\TestCase;

class InfrastructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleted_users_are_pruned_daily(): void
    {
        $events = collect($this->app->make(Schedule::class)->events())
            ->filter(fn (ScheduledEvent $event) => str_contains((string) $event->command, 'model:prune'));

        $this->assertCount(1, $events);
        $this->assertSame('0 0 * * *', $events->first()?->expression);
    }

    public function test_level_middleware_falls_back_to_guest_for_unknown_levels(): void
    {
        $user = $this->createUser(UserLevelEnum::GUEST);
        $request = Request::create('/');
        $request->setUserResolver(fn () => $user);

        $response = (new EnsureHasLevel)->handle($request, fn () => new Response('ok'), 42);

        $this->assertSame('ok', $response->getContent());
    }

    public function test_level_middleware_aborts_for_members_below_the_level(): void
    {
        $user = $this->createUser(UserLevelEnum::MEMBER);
        $request = Request::create('/');
        $request->setUserResolver(fn () => $user);

        $this->expectException(HttpException::class);
        (new EnsureHasLevel)->handle($request, fn () => new Response('ok'), UserLevelEnum::ADMINISTRATOR->value);
    }

    public function test_alert_component_maps_its_level_to_a_color(): void
    {
        $alert = new Alert(AlertLevelEnum::ERROR, 'Oups');

        $this->assertSame('red', $alert->color);
        $this->assertSame('Oups', $alert->message);
        $this->assertNotEmpty($alert->id);
        $this->assertSame('blue', (new Alert)->color);
    }

    public function test_flashed_alerts_are_displayed(): void
    {
        $this->actingAsMember();

        $this->withSession(['alert-'.AlertLevelEnum::SUCCESS->name => 'Tout est OK'])
            ->get(route('photos.index'))
            ->assertSee('Tout est OK');
    }

    public function test_brevo_mailers_are_registered(): void
    {
        config(['services.brevo.key' => 'xkeysib-test']);

        $this->assertInstanceOf(BrevoApiTransport::class, Mail::mailer('brevo')->getSymfonyTransport());
        // Legacy name used by existing .env files
        $this->assertInstanceOf(BrevoApiTransport::class, Mail::mailer('sendinblue')->getSymfonyTransport());
    }

    public function test_photo_factory_creates_image_files_without_network(): void
    {
        $photo = Photo::factory()->create();

        Storage::disk('photos')->assertExists([basename($photo->path), 'thumbs/'.basename($photo->thumb_path)]);
    }

    public function test_local_environment_seeder_runs(): void
    {
        $this->seed(LocalEnvironmentSeeder::class);

        $this->assertDatabaseHas('users', ['email' => 'michel@test.com', 'level' => UserLevelEnum::ADMINISTRATOR->value]);
        $this->assertDatabaseHas('users', ['email' => 'henry@test.com', 'level' => UserLevelEnum::MEMBER->value]);
        $this->assertDatabaseHas('users', ['email' => 'lucien@test.com', 'level' => UserLevelEnum::GUEST->value]);
        $this->assertGreaterThan(0, Photo::count());
    }
}
