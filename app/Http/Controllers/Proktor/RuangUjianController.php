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
        $peserta = $this->getPeserta();

        return Inertia::render('Proktor/RuangUjian', [
            'peserta' => $peserta
        ]);
    }

    public function refreshToken($id)
    {
        $peserta = UjianSiswa::findOrFail($id);

        // kalau token memang digenerate ulang, lakukan di sini
        // $peserta->token = Str::random(6);
        // $peserta->save();

        $this->broadcastPeserta();

        return response()->json(['success' => true]);
    }

    public function destroyPeserta($id)
    {
        UjianSiswa::findOrFail($id)->delete();

        $this->broadcastPeserta();

        return response()->json([
            'message' => 'Peserta berhasil dihapus!'
        ]);
    }

    /**
     * =========================
     * HELPER (PRIVATE METHOD)
     * =========================
     */
    private function getPeserta()
    {
        return UjianSiswa::with([
            'user.siswa.kelas',
            'soal.mapel',
        ])
        ->orderBy('id', 'DESC')
        ->get();
    }

    private function broadcastPeserta()
    {
        broadcast(new PesertaUpdated(
            $this->getPeserta()
        ));
    }
}
