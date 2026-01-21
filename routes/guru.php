<?php

use Inertia\Inertia;
use App\Http\Controllers\Guru\{
    QuizController,
    QuestController,
    RekapNilaiController,
    ExamRoomController
};

// Semua route guru, pakai auth, verified dan role guru
Route::middleware(['auth', 'verified', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {

    /** Soal / Quiz */
    Route::resource('soal', QuizController::class);

    /** Bank Soal / Quest */
    Route::get('/bank-soal/template', [QuestController::class, 'downloadTemplate'])
        ->name('bank-soal.template');

    Route::post('/bank-soal/import', [QuestController::class, 'import'])
        ->name('bank-soal.import');

    Route::resource('bank-soal', QuestController::class);

    Route::delete('/bank-soal/soal/{soal}/delete-all', [QuestController::class, 'destroyAll'])
        ->name('bank-soal.destroyAll');

    /** Rekap Nilai Ujian Siswa */
    Route::get('/rekap-nilai', [RekapNilaiController::class, 'index'])
    ->name('NilaiUjian.index');

    /** Rekap Nilai API untuk Vue */
    Route::get('/list-soal', [RekapNilaiController::class, 'listSoal']);
    Route::get('/list-mapel', [RekapNilaiController::class, 'listMapel']);
    Route::get('/list-kelas', [RekapNilaiController::class, 'listKelas']);
    Route::post('/rekap-filtered', [RekapNilaiController::class, 'rekapFiltered']);

    /** Ruang Ujian - daftar peserta */
    Route::get('/ruang-ujian', [ExamRoomController::class, 'index'])
        ->name('ruangUjian.index');

    /** Ambil data token terbaru */
    Route::get('/ruang-ujian/peserta/{peserta}/refresh-token', [ExamRoomController::class, 'refreshToken'])
        ->name('ruangUjian.refreshToken');

    /** Delete peserta AJAX */
    Route::delete('/ruang-ujian/peserta/{peserta}', [ExamRoomController::class, 'destroyPeserta'])
        ->name('ruangUjian.destroyPeserta');

});
