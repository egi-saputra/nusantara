<?php

namespace App\Http\Controllers\Proktor;

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

class NilaiController extends Controller
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
            ->join('users', 'users.id', '=', 'riwayat_ujian.user_id')
            ->join('siswa', 'siswa.user_id', '=', 'users.id')
            ->groupBy('riwayat_ujian.user_id', 'riwayat_ujian.soal_id')
            ->with(['user.siswa.kelas', 'soal.mapel'])
            ->orderBy('siswa.nama_lengkap', 'ASC') // ✔ A–Z
            ->get();

        return Inertia::render('Proktor/Nilai', [
            'rekap' => $rekap
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

    public function listSoal() {
    return Soal::select("id", "title")->orderBy("title")->get();
    }

    public function listMapel() {
        return Mapel::select("id", "mapel")->orderBy("mapel")->get();
    }

    public function listKelas() {
        return Kelas::select("id", "kelas")->orderBy("kelas")->get();
    }

    public function rekapFiltered(Request $req)
    {
        $query = DB::table("riwayat_ujian")
            ->leftJoin("soal", "soal.id", "=", "riwayat_ujian.soal_id")
            ->leftJoin("mapel", "mapel.id", "=", "soal.mapel_id")
            ->leftJoin("users", "users.id", "=", "riwayat_ujian.user_id")
            ->leftJoin("siswa", "siswa.user_id", "=", "users.id")
            ->leftJoin("kelas", "kelas.id", "=", "siswa.kelas_id")
            ->leftJoin("ujian_siswa", function ($join) {
                $join->on("ujian_siswa.user_id", "=", "riwayat_ujian.user_id")
                    ->on("ujian_siswa.soal_id", "=", "riwayat_ujian.soal_id");
            })
            ->select(
                "riwayat_ujian.user_id",
                "riwayat_ujian.soal_id",
                DB::raw("SUM(benar) as total_benar"),
                DB::raw("SUM(nilai) as total_nilai"),
                DB::raw("COUNT(quest_id) as total_soal"),

                // ⬅️ Status diambil dari tabel ujian_siswa
                DB::raw("MAX(ujian_siswa.status) as status")
            );

        // Filter Soal
        if ($req->soal_title) {
            $query->where("soal.title", $req->soal_title);
        }

        // Filter Mapel
        if ($req->mapel_id) {
            $query->where("mapel.id", $req->mapel_id);
        }

        // Filter Kelas
        if ($req->kelas_id) {
            $query->where("kelas.id", $req->kelas_id);
        }

        $query->groupBy("riwayat_ujian.user_id", "riwayat_ujian.soal_id");

        $rekap = $query->get();

        // inject relasi
        $rekap = $rekap->map(function ($item) {
            $item->user = \App\Models\User::with("siswa.kelas")->find($item->user_id);
            $item->soal = \App\Models\Soal::with("mapel")->find($item->soal_id);
            return $item;
        });

        return response()->json($rekap);
    }

}
