<?php

namespace App\Providers;

use Faker\Generator;
use Illuminate\Support\ServiceProvider;

class FakerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->afterResolving(function (mixed $instance) {
            if ($instance instanceof Generator) {
                $instance->addProvider(new \DavidBadura\FakerMarkdownGenerator\FakerProvider($instance));
            }
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
