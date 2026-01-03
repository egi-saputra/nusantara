<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Siswa\{
    FormController,
    UjianController
};

Route::middleware(['auth', 'verified', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {

        // ===== Form SISWA =====
        Route::get('form/create', [FormController::class, 'create'])
            ->name('form.create');

        Route::post('form', [FormController::class, 'store'])
            ->name('form.store');
        
         // ===== UJIAN SISWA =====
        Route::middleware(['activated'])->group(function () {
            Route::get('/ujian/token', [UjianController::class, 'tokenPage'])->name('ujian.token');
            Route::post('/ujian/validate-token', [UjianController::class, 'validateToken'])->name('ujian.validateToken');
            Route::post('/ujian/refresh-token/{soal}', [UjianController::class, 'refreshToken'])->name('ujian.refreshToken');
            Route::get('/ujian/preview/{id}', [UjianController::class, 'preview'])->name('ujian.preview');
            Route::get('/ujian/kerjakan/{id}', [UjianController::class, 'kerjakan'])->name('ujian.kerjakan');
            Route::post('/ujian/autosave', [UjianController::class, 'autosave'])->name('ujian.autosave');
            Route::post('/ujian/submit/{soal}', [UjianController::class, 'submitUjian'])->name('ujian.submit');
            Route::get('/ujian/finish', [UjianController::class, 'finish'])->name('ujian.finish');
        });

    });
