<?php

use App\Enums\UserLevelEnum;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\TripController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Account\SettingsController;
use App\Http\Controllers\Admin\UserController as AdminUserController;

// Home
Route::get('/', function () {
    if (auth()->check()) {
        return view('homepage.index');
    } else {
        return view('index');
    }
})->name('home');
Route::view('mentions-legales', 'legal')->name('legal');

// Profiles (access is checked in the controller)
Route::get('profil/{username?}', [ProfileController::class, 'show'])->name('profile.show');

Route::middleware('auth')->group(function () {
    // Settings
    Route::get('compte', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('compte', [SettingsController::class, 'update'])->name('settings.update');
    Route::delete('compte', [SettingsController::class, 'destroy'])->name('settings.destroy');

    Route::get('/virements', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/virements/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/virements', [PaymentController::class, 'store'])->name('payments.store');

    Route::middleware('level:' . UserLevelEnum::MEMBER->value)->group(function () {
        Route::get('matelots', [ProfileController::class, 'index'])->name('profile.index');
        Route::resources([
            'articles' => ArticleController::class,
            'albums' => AlbumController::class,
            'events' => EventController::class,
            'trips' => TripController::class,
        ]);
        Route::resource('photos', PhotoController::class)->except(['create']);
        Route::get('/photos/create/{album}', [PhotoController::class, 'create'])->name('photos.create');
    });

    Route::middleware('level:' . UserLevelEnum::ADMINISTRATOR->value)
        ->prefix('admin')
        ->group(function () {
            Route::get('/', function () {
                return view('admin.index');
            })->name('admin.index');
            Route::resource('users', AdminUserController::class, [
                'as' => 'admin',
            ])->except(['show', 'create', 'store']);
            Route::post('/storeImage', [ArticleController::class, 'storeImage'])->prefix('articles');
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

require __DIR__ . '/auth.php';
