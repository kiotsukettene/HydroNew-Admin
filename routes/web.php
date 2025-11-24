<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard', [
            'stats' => [
                'devices' => \App\Models\Device::where('user_id', auth()->id())->count(),
                'sensors' => \App\Models\Sensor::whereHas('device', function ($query) {
                    $query->where('user_id', auth()->id());
                })->count(),
                'setups' => \App\Models\HydroponicSetup::where('user_id', auth()->id())->count(),
            ],
        ]);
    })->name('dashboard');

    // Device routes
    Route::resource('devices', \App\Http\Controllers\DeviceController::class);
});

require __DIR__.'/settings.php';
