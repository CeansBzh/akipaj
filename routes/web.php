<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TripController;

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
    if(auth()->check()) {
        return view('homepage.index');
    } else {
        return view('index');
    }
});

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
            'photos' => PhotoController::class,
        ]);
        Route::resource('albums', AlbumController::class)->only(['index', 'show']);
        Route::resource('events', EventController::class);
        Route::resource('trips', TripController::class);
    });

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/', function () {
            return view('admin.index');
        })->name('admin.index');
        Route::get('/utilisateurs', function () {
            return view('admin.users');
        })->name('admin.users');
    });
});

require_once __DIR__ . '/auth.php';
