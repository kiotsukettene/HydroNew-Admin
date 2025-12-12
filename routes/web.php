<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Analytics\AnalyticsController;
use App\Http\Controllers\Devices\DeviceController;
use App\Http\Controllers\Feedback\FeedbackController;
use App\Http\Controllers\Dashboard\DashboardController;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('users/archived', [UserController::class, 'archived'])->name('users.archived');
    Route::patch('users/{id}/archive', [UserController::class, 'archive'])->name('users.archive');
    Route::patch('users/{id}/unarchive', [UserController::class, 'unarchive'])->name('users.unarchive');
    Route::resource('users', UserController::class);

    Route::resource('analytics', \App\Http\Controllers\Analytics\AnalyticsController::class);

    Route::get('devices/archived', [DeviceController::class, 'archived'])->name('devices.archived');
    Route::patch('devices/{id}/archive', [DeviceController::class, 'archive'])->name('devices.archive');
    Route::patch('devices/{id}/unarchive', [DeviceController::class, 'unarchive'])->name('devices.unarchive');
    Route::resource('devices', DeviceController::class);

     Route::resource('feedback', FeedbackController::class);


});

require __DIR__.'/settings.php';
