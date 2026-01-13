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
use App\Events\PesertaUpdated;

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

        // 1️⃣ Cek token siswa yang sudah ada
        $ujianSiswa = UjianSiswa::where('user_id', $userId)
            ->where('token', $inputToken)
            ->first();

        if ($ujianSiswa) {
            // Token valid → simpan session
            session(['token_validated' => $inputToken]);

            // Status Selesai → blokir
            if ($ujianSiswa->status === 'Selesai') {
                return redirect()->back()
                    ->with('error', 'Ujian sudah pernah diselesaikan.');
            }

            // Sedang Dikerjakan → langsung ke kerjakan
            if ($ujianSiswa->status === 'Sedang Dikerjakan' || $ujianSiswa->status === 'Terkunci') {
                // Terkunci tetap bisa masuk karena token valid, hanya mencegah back-browser
                return redirect()->route('siswa.ujian.kerjakan', $ujianSiswa->soal_id);
            }

            // Jika belum ada masalah → ke preview
            return redirect()->route('siswa.ujian.preview', $ujianSiswa->soal_id);
        }

        // 2️⃣ Cek token guru (token baru)
        $soal = Soal::where('token', $inputToken)->first();
        if (!$soal) {
            return redirect()->back()->with('error', 'Token tidak valid');
        }

        if ($soal->status !== 'Aktif') {
            return redirect()->back()->with('error', 'Ujian belum dimulai!');
        }

        // Simpan token valid di session
        session(['token_validated' => $soal->token]);

        // Langsung ke preview → buat record baru nanti saat kerjakan
        return redirect()->route('siswa.ujian.preview', $soal->id);
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
        $soal   = Soal::findOrFail($soal_id);

        $tokenValidated = session('token_validated', null);
        if (!$tokenValidated) {
            return redirect()->route('siswa.ujian.token')
                ->withErrors('Token belum valid, silahkan masukkan token.');
        }

        // Ambil record ujian siswa
        $ujianSiswa = UjianSiswa::where('user_id', $userId)
            ->where('soal_id', $soal_id)
            ->first();

        // 1️⃣ Buat record baru jika belum ada (siswa baru)
        if (!$ujianSiswa) {
            $ujianSiswa = UjianSiswa::create([
                'user_id'     => $userId,
                'soal_id'     => $soal_id,
                'waktu_mulai' => now(),
                'status'      => 'Sedang Dikerjakan',
                'token'       => $tokenValidated, // pakai token dari session
            ]);
        }

        // 2️⃣ Blokir hanya jika Selesai
        if ($ujianSiswa->status === 'Selesai') {
            return redirect()->route('siswa.ujian.token')
                ->withErrors('Ujian sudah selesai.');
        }

        // Status Terkunci → tetap bisa masuk karena token valid
        $ujianSiswa->update(['status' => 'Sedang Dikerjakan']);

        broadcast(new PesertaUpdated(
            $ujianSiswa->id,
            $ujianSiswa->status,
            $ujianSiswa->token
        ))->toOthers();

        // 3️⃣ TIMER (SINGLE SOURCE OF TRUTH)
        $timerKey = "ujian:{$soal_id}:user:{$userId}:end_time";
        if (!Cache::has($timerKey)) {
            Cache::put(
                $timerKey,
                now()->addMinutes($soal->waktu),
                now()->addMinutes($soal->waktu + 10)
            );
        }
        $sisaDetik = max(0, now()->diffInSeconds(Cache::get($timerKey), false));

        if ($sisaDetik <= 0) {
            $ujianSiswa->update(['status' => 'Selesai']);
            return redirect()->route('siswa.ujian.finish');
        }

        // 4️⃣ Ambil & acak soal
        $questIds = BankSoal::where('soal_id', $soal_id)->pluck('id')->toArray();
        if (empty($questIds)) {
            return redirect()->route('dashboard')
                ->withErrors('Soal ujian tidak valid.');
        }

        $redisKey = "ujian:{$soal_id}:user:{$userId}:urutan";
        $urutan   = Cache::get($redisKey);
        if (!is_array($urutan) || empty($urutan)) {
            $urutan = $questIds;
            if ($soal->tipe_soal === 'Acak') shuffle($urutan);
            Cache::put($redisKey, $urutan, now()->addMinutes($soal->waktu + 10));
        }

        $total = count($urutan);
        $no    = max(1, min((int) ($request->no ?? 1), $total));
        $quest = BankSoal::findOrFail($urutan[$no - 1]);

        // 5️⃣ Riwayat
        $riwayat = RiwayatUjian::where([
            'user_id'  => $userId,
            'soal_id'  => $soal_id,
            'quest_id' => $quest->id,
        ])->first();

        $answered = RiwayatUjian::where('user_id', $userId)
            ->where('soal_id', $soal_id)
            ->whereNotNull('jawaban')
            ->pluck('quest_id')
            ->toArray();

        return Inertia::render('Siswa/Ujian/Kerjakan', [
            'soal'       => $soal,
            'totalSoal'  => $total,
            'no'         => $no,
            'quest'      => $quest,
            'riwayat'    => $riwayat,
            'ujianSiswa' => $ujianSiswa,
            'durasi'     => $soal->waktu,
            'nomorList'  => $urutan,
            'sisaDetik'  => $sisaDetik,
            'answered'   => $answered,
        ]);
    }

    public function autosave(Request $request)
    {
        $request->validate([
            'soal_id'  => 'required|integer',
            'quest_id' => 'required|integer',
            'jawaban'  => 'nullable|string',
        ]);

        $userId = Auth::id();

        // ⏱ cek waktu ujian
        $timerKey = "ujian:{$request->soal_id}:user:{$userId}:end_time";
        if (!Cache::has($timerKey)) {
            return response()->json(['expired' => true], 419);
        }

        // =====================
        // SIMPAN KE REDIS
        // =====================
        $redisKey = "ujian:{$request->soal_id}:user:{$userId}:jawaban";
        $data = Cache::get($redisKey, []);
        $data[$request->quest_id] = $request->jawaban;

        Cache::put($redisKey, $data, now()->addMinutes(180));

        // =====================
        // BACKUP KE DB
        // =====================
        RiwayatUjian::updateOrCreate(
            [
                'user_id'  => $userId,
                'soal_id'  => $request->soal_id,
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

            // ESSAY
            if ($quest->tipe_soal === 'Essay') {
                RiwayatUjian::where([
                    'user_id'  => $userId,
                    'soal_id'  => $soal_id,
                    'quest_id' => $questId,
                ])->update([
                    'jawaban' => $jawaban,
                    'status'  => 'Selesai',
                ]);
                continue;
            }

            // PILIHAN GANDA
            $map = ['A'=>'opsi_a','B'=>'opsi_b','C'=>'opsi_c','D'=>'opsi_d','E'=>'opsi_e'];
            $benar = (($map[$jawaban] ?? null) === $quest->jawaban_benar);

            RiwayatUjian::where([
                'user_id'  => $userId,
                'soal_id'  => $soal_id,
                'quest_id' => $questId,
            ])->update([
                'jawaban' => $jawaban,
                'benar'   => $benar ? 1 : 0,
                'nilai'   => $benar ? $quest->nilai : 0,
                'status'  => 'Selesai',
            ]);
        }

        $ujian->update([
            'status' => 'Selesai',
            'waktu_selesai' => now()
        ]);

        broadcast(new PesertaUpdated(
            $ujian->id,
            $ujian->status,
            $ujian->token
        ))->toOthers();

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
            $ujian->update([
                'token' => str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT)
            ]);

            broadcast(new PesertaUpdated(
                $ujian->id,
                null, // status tidak berubah
                $ujian->token
            ))->toOthers();
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

    public function forceExit(Request $request, $soal_id)
    {
        $affected = UjianSiswa::where('user_id', Auth::id())
            ->where('soal_id', $soal_id)
            ->update([
                'status' => 'Terkunci'
            ]);

        // Hapus session token supaya back-browser tidak bisa
        session()->forget('token_validated');

        return response()->json([
            'status' => 'locked',
            'affected' => $affected
        ]);
    }

}
