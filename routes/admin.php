<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\{
    UserController,
    KelasController,
    KejuruanController,
    MapelController,
    SiswaController,
    GuruController
};

Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // ===== USER Management =====
        Route::resource('users', UserController::class)
            ->except(['show']);

        // ===== KELAS =====
        Route::get('kelas', [KelasController::class, 'index'])
            ->name('kelas.index');

        Route::get('kelas/create', [KelasController::class, 'create'])
            ->name('kelas.create');

        Route::post('kelas', [KelasController::class, 'store'])
            ->name('kelas.store');

        Route::get('kelas/{kelas}/edit', [KelasController::class, 'edit'])
            ->name('kelas.edit');

        Route::put('kelas/{kelas}', [KelasController::class, 'update'])
            ->name('kelas.update');

        Route::delete('kelas/{kelas}', [KelasController::class, 'destroy'])
            ->name('kelas.destroy');

        // ===== KEJURUAN =====
        Route::resource('kejuruan', KejuruanController::class)
            ->except(['show', 'edit']); // edit di-handle modal

        // ===== MAPEL =====
        Route::resource('mapel', MapelController::class)
            ->except(['show', 'edit']); // edit di-handle modal

        // ===== SISWA =====
        Route::resource('siswa', SiswaController::class)
            ->except(['show']);

        // ===== GURU =====
        Route::resource('guru', GuruController::class)
            ->except(['show', 'edit']);
    });
