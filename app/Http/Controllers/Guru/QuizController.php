<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Soal;
use App\Models\BankSoal;
use App\Models\Mapel;
use App\Models\Kelas;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class QuizController extends Controller
{
    // Generate token 6 digit unik
    private function generateToken()
    {
        do {
            $token = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (Soal::where('token', $token)->exists());

        return $token;
    }

    // Tampilkan daftar soal
    public function index()
    {
        $soal = Soal::where('user_id', auth()->id())
            ->with(['bank_soal', 'mapel'])
            ->paginate(10);

        return Inertia::render('Guru/Quiz/Index', [
            'soal' => $soal,
            'mapel' => Mapel::select('id', 'mapel')->orderBy('mapel')->get(),
            'title' => 'Quiz List',
        ]);
    }

    // Halaman create
    public function create()
    {
        return Inertia::render('Guru/Quiz/Create', [
            'mapel' => Mapel::select('id', 'mapel')
                            ->orderBy('mapel')
                            ->get(),
            'title' => 'Create / Add Quiz'
        ]);
    }

    // Simpan data ke database
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'mapel_id' => 'required|exists:mapel,id',
            'kelas' => 'required|string',
            'status' => 'required|in:Aktif,Tidak Aktif',
            'tipe_soal' => 'required|in:Acak,Berurutan',
            'waktu' => 'required|integer|min:1',
        ]);

        Soal::create([
            'user_id'   => Auth::id(),
            'title'     => $request->title,
            'mapel_id'     => $request->mapel_id,
            'kelas'     => $request->kelas,
            'status'    => $request->status,
            'tipe_soal' => $request->tipe_soal,
            'waktu'     => $request->waktu,
            'token'     => $this->generateToken(),
        ]);

        return redirect()->route('guru.soal.index')
                         ->with('success', 'Questions have been successfully created!');
    }

    // Hapus data
    public function destroy(Soal $soal)
    {
        // Hapus file fisik dari semua bank_soal terkait
        foreach ($soal->bank_soal as $bankSoal) {
            if ($bankSoal->link_lampiran && Storage::exists(str_replace('storage/', 'public/', $bankSoal->link_lampiran))) {
                Storage::delete(str_replace('storage/', 'public/', $bankSoal->link_lampiran));
            }
        }

        // Hapus soal, otomatis bank_soal juga ikut karena FK + cascade
        $soal->delete();

        return response()->json([
            'success' => 'Quiz has been successfully deleted!',
        ]);
    }

    // Show detail
    public function show(Soal $soal)
    {
        $soal->load('bank_soal', 'mapel');

        return Inertia::render('Guru/Quiz/Show', [
            'soal' => $soal,
            'mapel' => Mapel::select('id', 'mapel')->orderBy('mapel')->get(),
            'title' => 'Question List',
        ]);
    }
}
