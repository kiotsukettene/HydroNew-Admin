<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

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

    // Device routes
    Route::resource('devices', \App\Http\Controllers\DeviceController::class);

    // User routes
    Route::resource('users', \App\Http\Controllers\UserController::class);
});

require __DIR__.'/settings.php';
