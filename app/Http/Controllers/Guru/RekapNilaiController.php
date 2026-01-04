<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\UjianSiswa;
use App\Models\Soal;
use App\Models\Mapel;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\RiwayatUjian;
use Illuminate\Support\Facades\DB;

class RekapNilaiController extends Controller
{
    public function index()
    {
        $rekap = RiwayatUjian::select(
                'riwayat_ujian.user_id',
                'riwayat_ujian.soal_id',
                DB::raw('SUM(benar) as total_benar'),
                DB::raw('SUM(nilai) as total_nilai'),
                DB::raw('COUNT(quest_id) as total_soal'),
                DB::raw('MAX(riwayat_ujian.status) as status')
            )
            ->join('soal', 'soal.id', '=', 'riwayat_ujian.soal_id')
            ->join('users', 'users.id', '=', 'riwayat_ujian.user_id')
            ->join('siswa', 'siswa.user_id', '=', 'users.id')
            ->where('soal.user_id', auth()->id()) // 🔥 Filter soal yang dibuat guru login
            ->groupBy('riwayat_ujian.user_id', 'riwayat_ujian.soal_id')
            ->with(['user.siswa.kelas', 'soal.mapel'])
            ->orderBy('siswa.nama_lengkap', 'ASC')
            ->get();

        return Inertia::render('Guru/NilaiUjian', [
            'rekap' => $rekap,
            'title' => 'Assessment'
        ]);
    }

    public function rekapNilai()
    {
        $rekap = DB::table('riwayat_ujian')
            ->select(
                'user_id',
                'soal_id',
                DB::raw('SUM(benar) as total_benar'),
                DB::raw('SUM(nilai) as total_nilai'),
                DB::raw('COUNT(quest_id) as total_soal'),
                DB::raw('MAX(status) as status')
            )
            ->groupBy('user_id', 'soal_id')
            ->get();

        // Convert ke collection supaya bisa load relasi
        $rekap = $rekap->map(function ($item) {
            $item->user = \App\Models\User::with(['siswa.kelas'])->find($item->user_id);
            $item->soal = \App\Models\Soal::with(['mapel'])->find($item->soal_id);
            return $item;
        });

        return response()->json($rekap);
    }

    public function listSoal()
{
    $guruId = auth()->id(); 

    return Soal::with(['mapel', 'kelas']) // eager load relasi
        ->where('user_id', $guruId)
        ->orderBy('title')
        ->get();
}
  
public function listMapel()
{
    $guruId = auth()->id();
    $mapelIds = Soal::where('user_id', $guruId)->pluck('mapel_id')->unique();
    return Mapel::whereIn('id', $mapelIds)->orderBy('mapel')->get();
}

public function listKelas()
{
    $guruId = auth()->id();
    $kelasIds = RiwayatUjian::join('soal', 'soal.id', '=', 'riwayat_ujian.soal_id')
        ->where('soal.user_id', $guruId)
        ->join('siswa', 'siswa.user_id', '=', 'riwayat_ujian.user_id')
        ->pluck('siswa.kelas_id')
        ->unique();

    return Kelas::whereIn('id', $kelasIds)->orderBy('kelas')->get();
}

public function rekapFiltered(Request $req)
{
    $guruId = auth()->id();

    $query = DB::table("riwayat_ujian")
        ->leftJoin("soal", "soal.id", "=", "riwayat_ujian.soal_id")
        ->leftJoin("mapel", "mapel.id", "=", "soal.mapel_id")
        ->leftJoin("users", "users.id", "=", "riwayat_ujian.user_id")
        ->leftJoin("siswa", "siswa.user_id", "=", "users.id")
        ->leftJoin("kelas", "kelas.id", "=", DB::raw("CAST(siswa.kelas_id AS UNSIGNED)"))
        ->where("soal.user_id", $guruId) // filter guru login
        ->select(
            "riwayat_ujian.user_id",
            "riwayat_ujian.soal_id",
            DB::raw("SUM(benar) as total_benar"),
            DB::raw("SUM(nilai) as total_nilai"),
            DB::raw("COUNT(quest_id) as total_soal"),
            DB::raw("MAX(riwayat_ujian.status) as status")
        );

    if ($req->soal_title) {
        $query->whereIn('riwayat_ujian.soal_id', function($q) use ($req) {
            $q->select('id')
            ->from('soal')
            ->where('title', $req->soal_title);
        });
    }
    if ($req->mapel_id) $query->where("mapel.id", $req->mapel_id);
    if ($req->kelas_id) $query->where("siswa.kelas_id", $req->kelas_id);

    $query->groupBy("riwayat_ujian.user_id", "riwayat_ujian.soal_id");

    $rekap = $query->get();

    $rekap = $rekap->map(function ($item) {
        $item->user = \App\Models\User::with("siswa.kelas")->find($item->user_id);
        $item->soal = \App\Models\Soal::with("mapel")->find($item->soal_id);
        return $item;
    });

    return response()->json($rekap);
}
}
