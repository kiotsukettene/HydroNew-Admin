<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Analytics\AnalyticsController;
use App\Http\Controllers\Analytics\AnalyticsExportController;
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
    Route::get('dashboard/sensor-systems', [DashboardController::class, 'getSensorSystems'])->name('dashboard.sensor-systems');
    Route::get('dashboard/ph-tds-readings', [DashboardController::class, 'getPhTdsReadings'])->name('dashboard.ph-tds-readings');

    Route::get('users/archived', [UserController::class, 'archived'])->name('users.archived');
    Route::patch('users/{id}/archive', [UserController::class, 'archive'])->name('users.archive');
    Route::patch('users/{id}/unarchive', [UserController::class, 'unarchive'])->name('users.unarchive');
    Route::resource('users', UserController::class);

    Route::get('analytics/api/users-devices', [AnalyticsController::class, 'getUsersDevices'])->name('analytics.api.users-devices');
    Route::get('analytics/api/crops-harvest', [AnalyticsController::class, 'getCropsHarvest'])->name('analytics.api.crops-harvest');
    Route::get('analytics/api/yields', [AnalyticsController::class, 'getYields'])->name('analytics.api.yields');
    Route::get('analytics/api/water-treatment', [AnalyticsController::class, 'getWaterTreatment'])->name('analytics.api.water-treatment');
    Route::get('analytics/export/pdf', [AnalyticsExportController::class, 'exportPdf'])->name('analytics.export.pdf');
    Route::get('analytics/export/image', [AnalyticsExportController::class, 'exportImage'])->name('analytics.export.image');
    Route::resource('analytics', \App\Http\Controllers\Analytics\AnalyticsController::class);

    Route::get('devices/archived', [DeviceController::class, 'archived'])->name('devices.archived');
    Route::patch('devices/{id}/archive', [DeviceController::class, 'archive'])->name('devices.archive');
    Route::patch('devices/{id}/unarchive', [DeviceController::class, 'unarchive'])->name('devices.unarchive');
    Route::resource('devices', DeviceController::class);

     Route::resource('feedback', FeedbackController::class);


});

require __DIR__.'/settings.php';
