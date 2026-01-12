<?php

namespace App\Http\Controllers\Proktor;

use App\Http\Controllers\Controller;
use App\Models\UjianSiswa;
use App\Events\PesertaUpdated;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RuangUjianController extends Controller
{
    public function index()
    {
        return Inertia::render('Proktor/RuangUjian', [
            'peserta' => UjianSiswa::with([
                'user.siswa.kelas',
                'soal.mapel',
            ])->orderByDesc('id')->get()
        ]);
    }

    // 🔥 API untuk initial sync
    public function peserta()
    {
        return UjianSiswa::with([
            'user.siswa.kelas',
            'soal.mapel',
        ])->orderByDesc('id')->get();
    }

    public function refreshToken($id)
    {
        $peserta = UjianSiswa::findOrFail($id);

        // contoh
        // $peserta->update(['token' => Str::random(6)]);

        $peserta->refresh()->load([
            'user.siswa.kelas',
            'soal.mapel'
        ]);

        broadcast(new PesertaUpdated($peserta));

        return response()->json(['success' => true]);
    }

    public function destroyPeserta($id)
    {
        UjianSiswa::findOrFail($id)->delete();

        broadcast(new PesertaUpdated([
            'id' => $id,
            'deleted' => true
        ]));

        return response()->json(['message' => 'Peserta berhasil dihapus!']);
    }
}
