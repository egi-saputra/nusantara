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

    public function refreshToken($id)
    {
        $peserta = UjianSiswa::findOrFail($id);

        // optional:
        // $peserta->update(['token' => Str::random(6)]);

        return response()->json(['success' => true]);
    }

    public function destroyPeserta($id)
    {
        UjianSiswa::findOrFail($id)->delete();

        return response()->json(['message' => 'Peserta berhasil dihapus!']);
    }

    private function getPeserta()
    {
        return UjianSiswa::with([
            'user.siswa.kelas',
            'soal.mapel',
        ])->orderByDesc('id')->get();
    }
}
