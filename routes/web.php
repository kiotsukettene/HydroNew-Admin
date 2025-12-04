<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Analytics\AnalyticsController;
use App\Http\Controllers\Devices\DeviceController;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard', [
            'stats' => [
                'devices' => \App\Models\Device::count(),
                'sensors' => \App\Models\Sensor::count(),
                'setups' => \App\Models\HydroponicSetup::count(),
            ],
        ]);
    })->name('dashboard');

    Route::get('users/archived', [UserController::class, 'archived'])->name('users.archived');
    Route::resource('users', UserController::class);

    Route::resource('analytics', \App\Http\Controllers\Analytics\AnalyticsController::class);

    Route::get('devices/archived', [DeviceController::class, 'archived'])->name('devices.archived');
    Route::resource('devices', DeviceController::class);
});

require __DIR__.'/settings.php';
