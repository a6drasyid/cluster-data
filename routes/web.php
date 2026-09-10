<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemographicDataController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/dashboard/export/{format}', [DashboardController::class, 'export'])
    ->middleware(['auth', 'verified'])
    ->whereIn('format', ['pdf', 'excel', 'csv'])
    ->name('dashboard.export');

Route::get('/dashboard/klaster/{id}', [DashboardController::class, 'showCluster'])
    ->middleware(['auth', 'verified'])
    ->whereNumber('id')
    ->name('dashboard.cluster.detail');

Route::get('/dashboard/klaster/{id}/export/{format}', [DashboardController::class, 'exportCluster'])
    ->middleware(['auth', 'verified'])
    ->whereNumber('id')
    ->whereIn('format', ['pdf', 'excel', 'csv'])
    ->name('dashboard.cluster.export');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // CSV Import routes (placed before resource routing to prevent conflict with wildcard parameters)
    Route::get('/demographics/template/download', [DemographicDataController::class, 'downloadTemplate'])->name('demographics.template.download');
    Route::post('/demographics/import', [DemographicDataController::class, 'importCsv'])->name('demographics.import');

    Route::resource('demographics', DemographicDataController::class);
});

require __DIR__.'/auth.php';
