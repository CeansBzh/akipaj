<?php

use App\Http\Controllers\AlbumController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PhotoController;

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
    return view('index');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__ . '/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::get('/profil', function () {
        return view('profile');
    })->name('profile');

    Route::middleware('role:member')->group(function () {
        Route::resources([
            'photos' => PhotoController::class,
        ]);
        Route::resource('albums', AlbumController::class)->only(['index', 'show']);
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
