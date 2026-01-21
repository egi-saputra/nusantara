<?php

namespace App\Http\Controllers\Proktor;

use App\Http\Controllers\Controller;
use App\Models\UjianSiswa;
use Inertia\Inertia;

class RuangUjianController extends Controller
{
    public function index()
    {
        return Inertia::render('Proktor/RuangUjian', [
            'peserta' => $this->getPeserta()
        ]);
    }

    // ✅ TAMBAHKAN METHOD INI
    private function getPeserta()
    {
        return UjianSiswa::with([
            'user.siswa.kelas',
            'soal.mapel',
        ])->orderByDesc('id')->get();
    }

    public function peserta()
    {
        return response()->json([
            'peserta' => $this->getPeserta()
        ]);
    }

    public function refreshToken($id)
    {
        $peserta = UjianSiswa::findOrFail($id);

        // contoh kalau mau update token
        // $peserta->update(['token' => str_pad(random_int(0,999999),6,'0',STR_PAD_LEFT)]);

        return response()->json(['success' => true]);
    }

    public function destroyPeserta($id)
    {
        UjianSiswa::findOrFail($id)->delete();

        return response()->json(['message' => 'Peserta berhasil dihapus!']);
    }
}
