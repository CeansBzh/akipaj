<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\TripController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UserController as AdminUserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if (auth()->check()) {
        return view('homepage.index');
    } else {
        return view('index');
    }
});
Route::get('/mentions-legales', function () {
    return view('legal');
})->name('legal');

Route::middleware('auth')->group(function () {
    Route::get('/profil', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profil/modifier', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profil', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/virements', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/virements/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/virements', [PaymentController::class, 'store'])->name('payments.store');

    Route::middleware('role:member')->group(function () {
        Route::resources([
            'articles' => ArticleController::class,
            'albums' => AlbumController::class,
            'events' => EventController::class,
            'trips' => TripController::class,
        ]);
        Route::resource('photos', PhotoController::class)->except(['create']);
        Route::get('/photos/create/{album}', [PhotoController::class, 'create'])->name('photos.create');
    });

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/', function () {
            return view('admin.index');
        })->name('admin.index');
        Route::resource('users', AdminUserController::class, ['as' => 'admin'])->except(['show']);
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

require_once __DIR__ . '/auth.php';
