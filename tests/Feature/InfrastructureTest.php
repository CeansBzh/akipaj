<?php

namespace Tests\Feature;

use App\Enums\AlertLevelEnum;
use App\Enums\UserLevelEnum;
use App\Http\Middleware\EnsureHasLevel;
use App\Models\Photo;
use App\Models\Trip;
use App\View\Components\Alert;
use Illuminate\Console\Scheduling\Event as ScheduledEvent;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Bridge\Sendinblue\Transport\SendinblueApiTransport;
use Stripe\StripeClient;
use Symfony\Component\HttpKernel\Exception\HttpException;
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

    public function test_where_like_macro_searches_several_columns(): void
    {
        $match = Trip::factory()->create(['title' => 'Rien', 'description' => 'Une REGATE au large']);
        Trip::factory()->create(['title' => 'Autre', 'description' => 'Croisière']);

        $this->assertSame([$match->id], Trip::whereLike(['title', 'description'], 'regate')->pluck('id')->all());
        $this->assertSame(2, Trip::whereLike('title', '')->count());
    }

    public function test_level_middleware_falls_back_to_guest_for_unknown_levels(): void
    {
        $user = $this->createUser(UserLevelEnum::GUEST);
        $request = Request::create('/');
        $request->setUserResolver(fn () => $user);

        $response = (new EnsureHasLevel())->handle($request, fn () => new Response('ok'), 42);

        $this->assertSame('ok', $response->getContent());
    }

    public function test_level_middleware_aborts_for_members_below_the_level(): void
    {
        $user = $this->createUser(UserLevelEnum::MEMBER);
        $request = Request::create('/');
        $request->setUserResolver(fn () => $user);

        $this->expectException(HttpException::class);
        (new EnsureHasLevel())->handle($request, fn () => new Response('ok'), UserLevelEnum::ADMINISTRATOR->value);
    }

    public function test_alert_component_maps_its_level_to_a_color(): void
    {
        $alert = new Alert(AlertLevelEnum::ERROR, 'Oups');

        $this->assertSame('red', $alert->color);
        $this->assertSame('Oups', $alert->message);
        $this->assertNotEmpty($alert->id);
        $this->assertSame('blue', (new Alert())->color);
    }

    public function test_flashed_alerts_are_displayed(): void
    {
        $this->actingAsMember();

        $this->withSession(['alert-' . AlertLevelEnum::SUCCESS->name => 'Tout est OK'])
            ->get(route('photos.index'))
            ->assertSee('Tout est OK');
    }

    public function test_stripe_client_can_be_resolved_without_configuration(): void
    {
        config(['stripe.secret_key' => null]);

        $this->assertInstanceOf(StripeClient::class, $this->app->make(StripeClient::class));
    }

    public function test_sendinblue_mailer_is_registered(): void
    {
        config(['services.sendinblue.key' => 'xkeysib-test']);

        $transport = Mail::mailer('sendinblue')->getSymfonyTransport();

        $this->assertInstanceOf(SendinblueApiTransport::class, $transport);
    }

    public function test_photo_factory_creates_image_files_without_network(): void
    {
        $photo = Photo::factory()->create();

        $this->assertFileExists($this->publicPath . '/storage/photos/' . basename($photo->path));
        $this->assertFileExists($this->publicPath . '/storage/photos/thumbs/' . basename($photo->thumb_path));
    }

    public function test_local_environment_seeder_runs(): void
    {
        $this->seed(\Database\Seeders\LocalEnvironmentSeeder::class);

        $this->assertDatabaseHas('users', ['email' => 'michel@test.com', 'level' => UserLevelEnum::ADMINISTRATOR->value]);
        $this->assertDatabaseHas('users', ['email' => 'henry@test.com', 'level' => UserLevelEnum::MEMBER->value]);
        $this->assertDatabaseHas('users', ['email' => 'lucien@test.com', 'level' => UserLevelEnum::GUEST->value]);
        $this->assertGreaterThan(0, Photo::count());
    }
}
