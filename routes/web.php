<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Analytics\AnalyticsController;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::get('users/archived', [UserController::class, 'archived'])->name('users.archived');
    Route::resource('users', UserController::class);

    Route::resource('analytics', \App\Http\Controllers\Analytics\AnalyticsController::class);
});

require __DIR__.'/settings.php';
