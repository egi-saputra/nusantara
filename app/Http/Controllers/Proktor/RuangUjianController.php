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
            ])->orderBy('id', 'DESC')->get()
        ]);
    }

    public function refreshToken($id)
    {
        $peserta = UjianSiswa::with([
            'user.siswa.kelas',
            'soal.mapel',
        ])->findOrFail($id);

        // $peserta->token = Str::random(6);
        // $peserta->save();

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

