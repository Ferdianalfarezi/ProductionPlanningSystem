<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MesinController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\PreviewAndonController;
use App\Http\Controllers\AndonMesinController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });
    
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
    
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Mesin CRUD
    Route::get('mesin/data', [MesinController::class, 'data'])->name('mesin.data');
    Route::resource('mesin', MesinController::class);

    // Items Routes
    Route::prefix('items')->name('items.')->group(function () {
        Route::get('/template/download', [ItemController::class, 'template'])->name('template');
        Route::post('/import', [ItemController::class, 'import'])->name('import');
        Route::get('/data', [ItemController::class, 'data'])->name('data');
        Route::get('/', [ItemController::class, 'index'])->name('index');
        Route::post('/', [ItemController::class, 'store'])->name('store');
        Route::get('/{item}', [ItemController::class, 'show'])->name('show');
        Route::put('/{item}', [ItemController::class, 'update'])->name('update');
        Route::delete('/{item}', [ItemController::class, 'destroy'])->name('destroy');
    });

    // Plannings Routes
    Route::prefix('plannings')->name('plannings.')->group(function () {
        Route::get('/template/download', [PlanningController::class, 'template'])->name('template');
        Route::post('/import', [PlanningController::class, 'import'])->name('import');
        Route::post('/clear', [PlanningController::class, 'clear'])->name('clear');
        Route::get('/data', [PlanningController::class, 'data'])->name('data');
        Route::get('/', [PlanningController::class, 'index'])->name('index');
        Route::post('/', [PlanningController::class, 'store'])->name('store');
        Route::get('/{planning}', [PlanningController::class, 'show'])->name('show');
        Route::put('/{planning}', [PlanningController::class, 'update'])->name('update');
        Route::delete('/{planning}', [PlanningController::class, 'destroy'])->name('destroy');
    });

    // routes/web.php
    Route::prefix('andon')->group(function () {
        // Routes Preview Andon
        Route::get('/preview', [PreviewAndonController::class, 'index'])->name('andon.preview');
        Route::post('/sync', [PreviewAndonController::class, 'sync'])->name('andon.sync');
        Route::post('/{id}/toggle-active', [PreviewAndonController::class, 'toggleActive'])->name('andon.toggle-active');
        Route::post('/update-shift', [PreviewAndonController::class, 'updateShift'])->name('andon.update-shift');
        Route::post('/{id}/update-actual', [PreviewAndonController::class, 'updateActual'])->name('andon.update-actual');
        Route::get('/{id}/detail', [PreviewAndonController::class, 'detail'])->name('andon.detail');
        Route::post('/reorder', [PreviewAndonController::class, 'reorder'])->name('andon.reorder');
        Route::post('/bulk-update', [PreviewAndonController::class, 'bulkUpdate'])->name('andon.bulk-update');
        Route::get('/get-mesin-data', [PreviewAndonController::class, 'getMesinData'])->name('andon.get-mesin-data');
        
        // Submit dari Preview Andon ke Andon Mesin
        Route::post('/submit-to-mesin', [PreviewAndonController::class, 'submitToAndonMesin'])->name('andon.submit-to-mesin');
        
        // Routes Andon Mesin
        Route::prefix('mesin')->group(function () {
            Route::get('/', [AndonMesinController::class, 'index'])->name('andon.mesin');
            Route::get('/data', [AndonMesinController::class, 'getData'])->name('andon.mesin.data');
            Route::post('/', [AndonMesinController::class, 'store'])->name('andon.mesin.store');
            Route::get('/last-submission/{mesinId}', [AndonMesinController::class, 'getLastSubmissionStatus'])->name('andon.mesin.last-submission');
            Route::get('/dates', [AndonMesinController::class, 'getAvailableDates'])->name('andon.mesin.dates');
            Route::post('/export', [AndonMesinController::class, 'export'])->name('andon.mesin.export');
            Route::delete('/{id}', [AndonMesinController::class, 'destroy'])->name('andon.mesin.destroy');
        });
    });

    // Andon Lane
Route::get('/andon/lane', [App\Http\Controllers\AndonLaneController::class, 'index'])->name('andon.lane');
Route::get('/andon/lane/data', [App\Http\Controllers\AndonLaneController::class, 'getData'])->name('andon.lane.data');
    
});