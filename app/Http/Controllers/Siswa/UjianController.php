<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Soal;
use App\Models\Mapel;
use App\Models\BankSoal;
use App\Models\RiwayatUjian;
use App\Models\UjianSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;

class UjianController extends Controller
{
    public function tokenPage()
    {
        $user = Auth::user();

        return Inertia::render('Siswa/Ujian/Token', [
            'title' => 'Exam Entrance'
        ]);
    }

    // Validasi token input siswa
    public function validateToken(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        $userId = Auth::id();
        $inputToken = $request->token;

        // Cek token ujian siswa
        $ujianSiswa = UjianSiswa::where('user_id', $userId)
            ->where('token', $inputToken)
            ->first();

        if ($ujianSiswa) {
            return redirect()->route('siswa.ujian.preview', $ujianSiswa->soal_id);
        }

        // Cek token guru
        $soal = Soal::where('token', $inputToken)->first();
        if (!$soal) {
            return redirect()->back()->with('error', 'Token tidak valid');
        }

        // Cek status soal
        if ($soal->status !== 'Aktif') {
            return redirect()->back()->with('error', 'Ujian belum dimulai!');
        }

        $soalId = $soal->id;

        // Cek apakah sudah pernah ikut
        $existing = UjianSiswa::where('user_id', $userId)
            ->where('soal_id', $soalId)
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Token ujian sudah digunakan, silahkan gunakan token yang baru!');
        }

        return redirect()->route('siswa.ujian.preview', $soalId);
    }

    public function preview($soal_id)
    {
        $soal = Soal::with('mapel')->findOrFail($soal_id);
        $jumlahSoal = BankSoal::where('soal_id', $soal_id)->count();

        return Inertia::render('Siswa/Ujian/Preview', [
            'soal' => $soal,
            'jumlahSoal' => $jumlahSoal,
            'mapel' => Mapel::select('id', 'mapel')->orderBy('mapel')->get(),
        ]);
    }

    public function kerjakan(Request $request, $soal_id)
    {
        $userId = Auth::id();
        $soal = Soal::findOrFail($soal_id);

        // Ambil atau buat data ujian siswa
        $ujianSiswa = UjianSiswa::firstOrCreate(
            ['user_id' => $userId, 'soal_id' => $soal_id],
            [
                'waktu_mulai' => now(),
                'status' => 'Sedang Dikerjakan',
                'token' => str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            ]
        );

        if ($ujianSiswa->status === 'Belum Dikerjakan') {
            $ujianSiswa->update(['status' => 'Sedang Dikerjakan']);
        }

        if ($ujianSiswa->status === 'Selesai') {
            return redirect()->route('dashboard')
                ->with('error', 'Anda sudah menyelesaikan ujian ini.');
        }

        // Ambil semua quest untuk soal ini
        // $questIds = BankSoal::where('soal_id', $soal_id)->pluck('id')->toArray();
        // $nomorList = $questIds;
        $questIds = BankSoal::where('soal_id', $soal_id)->pluck('id')->toArray();

        $redisKey = "ujian:{$soal_id}:user:{$userId}:urutan";
        $urutan = Cache::get($redisKey);

        if (!is_array($urutan) || empty($urutan)) {
            $urutan = $questIds;

            if ($soal->tipe_soal === 'Acak') {
                shuffle($urutan);
            }

            Cache::put(
                $redisKey,
                $urutan,
                now()->addMinutes($soal->waktu + 10)
            );
        }

        $urutan = Cache::get($redisKey);
        $nomorList = $urutan;

        // Buat riwayat jawaban jika belum ada
        // foreach ($questIds as $qid) {
        //     RiwayatUjian::firstOrCreate(
        //         [
        //             'user_id' => $userId,
        //             'soal_id' => $soal_id,
        //             'quest_id' => $qid,
        //         ],
        //         [
        //             'jawaban' => null,
        //             'benar' => 0,
        //             'nilai' => 0,
        //             'status' => 'Belum Dikerjakan',
        //         ]
        //     );
        // }

        // Urutan soal (acak jika tipe soal acak)
        // $sessionKey = "urutan_soal_{$soal_id}_{$userId}";
        // if (!session()->has($sessionKey)) {
        //     $urutan = $questIds;
        //     if ($soal->tipe_soal === 'Acak') shuffle($urutan);
        //     session([$sessionKey => $urutan]);
        // }

        // $redisKey = "ujian:{$soal_id}:user:{$userId}:urutan";

        // if (!Cache::has($redisKey)) {
        //     $urutan = $questIds;
        //     if ($soal->tipe_soal === 'Acak') shuffle($urutan);

        //     Cache::put($redisKey, $urutan, now()->addMinutes($soal->waktu + 10));
        // }

        // $urutan = Cache::get($redisKey);
        $total = count($urutan);
            if ($total === 0) {
                return redirect()
                    ->route('dashboard')
                    ->with('error', 'Soal ujian tidak valid.');
            }

        $no = max(1, min((int) ($request->no ?? 1), $total));
        $questId = $urutan[$no - 1];
        $quest = BankSoal::findOrFail($questId);

        $riwayat = RiwayatUjian::where([
            'user_id' => $userId,
            'soal_id' => $soal_id,
            'quest_id' => $questId,
        ])->first();

        // Hitung sisa waktu
        $timerKey = "ujian:{$soal_id}:user:{$userId}:end_time";

        if (!Cache::has($timerKey)) {
            Cache::put(
                $timerKey,
                now()->addMinutes($soal->waktu),
                now()->addMinutes($soal->waktu + 10)
            );
        }

        $sisaDetik = max(0, now()->diffInSeconds(Cache::get($timerKey), false));

        // $waktuMulai = \Carbon\Carbon::parse($ujianSiswa->waktu_mulai);
        // $waktuSelesai = $waktuMulai->copy()->addMinutes($soal->waktu ?? 0);
        // $sisaDetik = max(0, now()->diffInSeconds($waktuSelesai, false));

        // Daftar soal yang sudah dijawab
        $answered = RiwayatUjian::where('user_id', $userId)
            ->where('soal_id', $soal_id)
            ->whereNotNull('jawaban')
            ->pluck('quest_id')
            ->toArray();

        return Inertia::render('Siswa/Ujian/Kerjakan', [
            'soal' => $soal,
            'totalSoal' => $total,
            'no' => $no,
            'quest' => $quest,
            'riwayat' => $riwayat,
            'ujianSiswa' => $ujianSiswa,
            'durasi' => $soal->waktu,
            'nomorList' => $nomorList,
            'sisaDetik' => $sisaDetik,
            'answered' => $answered,
        ]);
    }

    // public function autosave(Request $request)
    // {
    //     $request->validate([
    //         'soal_id' => 'required',
    //         'quest_id' => 'required',
    //         'jawaban' => 'nullable|string',
    //     ]);

    //     $userId = Auth::id();
    //     $quest = BankSoal::find($request->quest_id);

    //     if ($quest) {
    //         $map = [
    //             'A' => 'opsi_a',
    //             'B' => 'opsi_b',
    //             'C' => 'opsi_c',
    //             'D' => 'opsi_d',
    //             'E' => 'opsi_e',
    //         ];

    //         $jawab = $request->jawaban;
    //         $benar = (($map[$jawab] ?? null) === $quest->jawaban_benar);

    //         RiwayatUjian::where([
    //             'user_id' => $userId,
    //             'soal_id' => $request->soal_id,
    //             'quest_id' => $request->quest_id,
    //         ])->update([
    //             'jawaban' => $jawab,
    //             'benar'   => $benar ? 1 : 0,
    //             'nilai'   => $benar ? $quest->nilai : 0,
    //             'status'  => $jawab ? 'Sedang Dikerjakan' : 'Belum Dikerjakan',
    //         ]);
    //     }

    //     // 🔴 PENTING: JANGAN JSON
    //     return back();
    //     // ✅ Kembalikan JSON agar Inertia tahu request berhasil
    //     // return response()->json([
    //     //     'success' => true,
    //     //     'jawaban' => $request->jawaban
    //     // ]);
    // }
    public function autosave(Request $request)
    {
        $request->validate([
            'soal_id' => 'required|integer',
            'quest_id' => 'required|integer',
            'jawaban' => 'nullable|in:A,B,C,D,E',
        ]);

        $userId = Auth::id();

        // ⏱ cek waktu ujian
        $timerKey = "ujian:{$request->soal_id}:user:{$userId}:end_time";
        if (!Cache::has($timerKey)) {
            return response()->json(['expired' => true], 419);
        }

        /* =====================
        1️⃣ SIMPAN KE REDIS
        ====================== */
        $redisKey = "ujian:{$request->soal_id}:user:{$userId}:jawaban";
        $data = Cache::get($redisKey, []);
        $data[$request->quest_id] = $request->jawaban;

        Cache::put($redisKey, $data, now()->addMinutes(180));

        /* =====================
        2️⃣ BACKUP KE DB (RINGAN)
        ====================== */
        RiwayatUjian::updateOrCreate(
            [
                'user_id' => $userId,
                'soal_id' => $request->soal_id,
                'quest_id' => $request->quest_id,
            ],
            [
                'jawaban' => $request->jawaban,
                'status'  => 'Sedang Dikerjakan',
            ]
        );

        return response()->noContent();
    }

    public function submitUjian(Request $request, $soal_id)
    {
        $userId = Auth::id();

        $ujian = UjianSiswa::where([
            'user_id' => $userId,
            'soal_id' => $soal_id
        ])->first();

        if ($ujian && $ujian->status === 'Selesai') {
            return redirect()->route('dashboard')
                ->with('warning', 'Ujian sudah diselesaikan.');
        }

        $riwayat = RiwayatUjian::where('user_id', $userId)
            ->where('soal_id', $soal_id)
            ->get();

        $map = ['A'=>'opsi_a','B'=>'opsi_b','C'=>'opsi_c','D'=>'opsi_d','E'=>'opsi_e'];

        $key = "ujian:{$soal_id}:user:{$userId}:jawaban";
        $jawabanRedis = Cache::get($key, []);

        foreach ($jawabanRedis as $questId => $jawaban) {
            $quest = BankSoal::find($questId);
            if (!$quest) continue;

            // $map = ['A'=>'opsi_a','B'=>'opsi_b','C'=>'opsi_c','D'=>'opsi_d','E'=>'opsi_e'];
            $benar = (($map[$jawaban] ?? null) === $quest->jawaban_benar);

            RiwayatUjian::where([
                'user_id' => $userId,
                'soal_id' => $soal_id,
                'quest_id' => $questId,
            ])->update([
                'jawaban' => $jawaban,
                'benar' => $benar ? 1 : 0,
                'nilai' => $benar ? $quest->nilai : 0,
                'status' => 'Selesai',
            ]);
        }

        $ujian->update([
            'status' => 'Selesai',
            'waktu_selesai' => now()
        ]);

        Cache::forget($key);
        Cache::forget("ujian:{$soal_id}:user:{$userId}:urutan");
        Cache::forget("ujian:{$soal_id}:user:{$userId}:end_time");
        // session()->forget("urutan_soal_{$soal_id}_{$userId}");

        // return redirect()->route('siswa.ujian.finish')
        //     ->with('success', 'Ujian berhasil disubmit!');
        return response()->noContent();
    }

    public function refreshToken(Request $request, $soal_id)
    {
        $ujian = UjianSiswa::where([
            'user_id' => Auth::id(),
            'soal_id' => $soal_id
        ])->first();

        if ($ujian) {
            // $ujian->update(['token' => Str::upper(Str::random(6))]);
            $ujian->update(['token' => str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT)]);
        }

        return response()->json(['success' => true]);
    }

    public function finish()
    {
        // Kalau mau, bisa ambil data user terbaru
        $userId = Auth::id();
        $ujianSelesai = UjianSiswa::where('user_id', $userId)
            ->where('status', 'Selesai')
            ->latest('waktu_selesai')
            ->first();

        return Inertia::render('Siswa/Ujian/Finish', [
            'ujianSiswa' => $ujianSelesai,
        ]);
    }

}
