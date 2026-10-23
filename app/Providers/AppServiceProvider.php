<?php

namespace App\Providers;

use App\Models\User;
use Carbon\Carbon;
use DavidBadura\FakerMarkdownGenerator\FakerProvider as MarkdownFakerProvider;
use Faker\Generator as FakerGenerator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Markdown generator for the factories (dev dependency, only used when Faker is resolved)
        $this->app->afterResolving(FakerGenerator::class, function (FakerGenerator $faker) {
            $faker->addProvider(new MarkdownFakerProvider($faker));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));

        Route::bind('username', fn (string $username) => User::findByUsername($username));

        // Brevo (formerly Sendinblue) API transport. The "sendinblue" mailer name is kept for existing .env files.
        Mail::extend('brevo', function () {
            return (new BrevoTransportFactory)->create(
                new Dsn('brevo+api', 'default', config('services.brevo.key')),
            );
        });
    }
}
