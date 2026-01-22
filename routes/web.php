<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MesinController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\PreviewAndonController;
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

    Route::prefix('preview-andon')->group(function () {
        Route::get('/', [PreviewAndonController::class, 'index'])->name('preview-andon.index');
        Route::post('/sync', [PreviewAndonController::class, 'sync'])->name('preview-andon.sync');
        Route::post('/update-shift', [PreviewAndonController::class, 'updateShift'])->name('preview-andon.update-shift');
        Route::post('/{id}/update-actual', [PreviewAndonController::class, 'updateActual'])->name('preview-andon.update-actual');
        Route::get('/{id}/detail', [PreviewAndonController::class, 'detail'])->name('preview-andon.detail');
        Route::get('/{id}/edit', [PreviewAndonController::class, 'edit'])->name('preview-andon.edit'); // Tambah ini
        Route::post('/reorder', [PreviewAndonController::class, 'reorder'])->name('preview-andon.reorder');
        Route::post('/{id}/toggle-status', [PreviewAndonController::class, 'toggleStatus'])->name('preview-andon.toggle-status');
    });
});