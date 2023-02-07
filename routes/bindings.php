<?php

use Illuminate\Support\Facades\Route;

Route::bind('username', function (string $username) {
    return App\Models\User::findByUsername($username);
});
