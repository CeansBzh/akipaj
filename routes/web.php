<?php

use App\Enums\UserLevelEnum;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Account\SettingsController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\TripController;
use App\Models\Album;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', function () {
    if (auth()->check()) {
        return view('homepage.index');
    } else {
        return view('index');
    }
})->name('home');
Route::view('mentions-legales', 'legal')->name('legal');
// Les événements sont devenus des sorties : leurs anciens liens, gardés dans les agendas des membres, mènent au programme
Route::permanentRedirect('events/{event?}', '/');

// Profiles (access is checked in the controller)
Route::get('profil/{username?}', [ProfileController::class, 'show'])->name('profile.show');

// Liens de partage d'une photo ou d'un album, ouverts sans compte jusqu'à leur expiration
Route::prefix('partage/{share}')->name('shares.')->controller(ShareController::class)->group(function () {
    Route::get('/', 'show')->name('show');
    Route::get('photos', 'photos')->name('photos');
    Route::get('photos/{photo}', 'file')->whereNumber('photo')->name('file');
    Route::get('photos/{photo}/vignette', 'thumbnail')->whereNumber('photo')->name('thumbnail');
});

Route::middleware('auth')->group(function () {
    // Settings
    Route::get('compte', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('compte', [SettingsController::class, 'update'])->name('settings.update');
    Route::delete('compte', [SettingsController::class, 'destroy'])->name('settings.destroy');

    Route::middleware('level:'.UserLevelEnum::MEMBER->value)->group(function () {
        Route::get('matelots', [ProfileController::class, 'index'])->name('profile.index');
        Route::resource('trips', TripController::class);
        Route::resource('albums', AlbumController::class)->except(['index', 'show']);
        // Les albums n'ont plus de pages à eux : leurs anciens liens mènent à la galerie, filtrée sur l'album
        Route::get('albums', fn () => redirect()->route('photos.index', status: 301));
        Route::get('albums/{album}', fn (Album $album) => redirect()->route('photos.index', ['albums' => [$album->id]], 301))
            ->whereNumber('album');
        Route::resource('photos', PhotoController::class)->except(['create']);
        Route::get('/photos/create/{album}', [PhotoController::class, 'create'])->name('photos.create');
        // Keeps the URL of the photos uploaded when they were in the public storage
        Route::get('/storage/photos/{path}', [PhotoController::class, 'file'])
            ->where('path', '(thumbs/)?\w[\w.-]*')
            ->name('photos.file');
    });

    Route::middleware('level:'.UserLevelEnum::ADMINISTRATOR->value)
        ->prefix('admin')
        ->group(function () {
            Route::get('/', function () {
                return view('admin.index');
            })->name('admin.index');
            Route::resource('users', AdminUserController::class, [
                'as' => 'admin',
            ])->except(['show', 'create', 'store']);
            Route::get('/reset', function () {
                Artisan::call('route:clear');
                Artisan::call('cache:clear');
                Artisan::call('event:clear');
                Artisan::call('view:clear');
                Artisan::call('config:clear');
                Artisan::call('config:cache');

                return redirect()->route('admin.index');
            });
        });
});

require __DIR__.'/auth.php';
