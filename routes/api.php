<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Proktor\NilaiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

    // Routes untuk Proktor
    Route::get('/rekap-nilai', [NilaiController::class, 'rekapNilai']);
    Route::get('/list-soal', [NilaiController::class, 'listSoal']);
    Route::get('/list-mapel', [NilaiController::class, 'listMapel']);
    Route::get('/list-kelas', [NilaiController::class, 'listKelas']);
    Route::post('/rekap-filtered', [NilaiController::class, 'rekapFiltered']);
