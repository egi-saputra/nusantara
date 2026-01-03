<?php

namespace App\Http\Controllers\Proktor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Soal;
use App\Models\BankSoal;
use App\Models\Mapel;
use App\Models\Kelas;
use Inertia\Inertia;

class SoalController extends Controller
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
        // Load relasi bank_soal supaya bisa dihitung di frontend
        $soal = Soal::with('bank_soal')
        ->with(['bank_soal', 'mapel'])
        ->paginate(10);
        
        // $soal = Soal::where('user_id', auth()->id())
        //     ->with(['bank_soal', 'mapel'])
        //     ->paginate(10);

        return Inertia::render('Proktor/Soal/Index', [
            'soal' => $soal,
            'mapel' => Mapel::select('id', 'mapel')->orderBy('mapel')->get(),
            'title' => 'Quiz Management',
        ]);
    }

    // Halaman create
    public function create()
    {
        return Inertia::render('Proktor/Soal/Create', [
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

        return redirect()->route('proktor.soal.index')
                         ->with('success', 'Soal berhasil dibuat!');
    }

    public function edit(Soal $soal)
    {
        // Load relasi bank_soal agar frontend dapat mengecek panjangnya
        $soal->load('bank_soal');

        // Ambil salah satu nilai per butir soal
        $nilaiPerSoal = $soal->bank_soal()->first()?->nilai ?? 0;

        // Ambil daftar mapel untuk select option
        $mapel = Mapel::select('id', 'mapel')
                    ->orderBy('mapel')
                    ->get();

        return Inertia::render('Proktor/Soal/Edit', [
            'soal' => $soal,
            'nilai_per_soal' => $nilaiPerSoal,
            'mapel' => $mapel,
        ]);
    }

    // Update data quiz (mapel_id, kelas, dll)
    public function update(Request $request, Soal $soal)
    {
        $request->validate([
            'title' => 'required|string',
            'mapel_id' => 'required|exists:mapel,id', // wajib ada di database mapel
            'kelas' => 'required|string',
            'status' => 'required|in:Aktif,Tidak Aktif',
            'tipe_soal' => 'required|in:Berurutan,Acak',
            'waktu' => 'required|integer|min:1',
            // 'token' => 'required|numeric|digits:6',
            'token' => 'required|string|size:6',
        ]);

        $soal->update([
            'title'     => $request->title,
            'mapel_id'  => $request->mapel_id,
            'kelas'     => $request->kelas,
            'status'    => $request->status,
            'tipe_soal' => $request->tipe_soal,
            'waktu'     => $request->waktu,
            'token'     => $request->token,
        ]);

        return redirect()->route('proktor.soal.index')
                        ->with('success', 'Data Quiz berhasil diperbarui!');
    }

    // Hapus data
    public function destroy(Soal $soal)
    {
        $soal->delete();

        return response()->json([
            'success' => 'Soal berhasil dihapus!',
        ]);
    }

    // Show detail
    public function show(Soal $soal)
    {
        $soal->load('bank_soal', 'mapel');

        return Inertia::render('Proktor/Soal/Show', [
            'soal' => $soal,
            'mapel' => Mapel::select('id', 'mapel')->orderBy('mapel')->get(),
            'title' => 'Quiz Management',
        ]);
    }

    public function updateNilai(Request $request, Soal $soal)
    {
        $request->validate([
            'nilai' => 'required|numeric|min:0',
        ]);

        if (!$soal->bank_soal()->exists()) {
            return response()->json(['error' => 'Tidak ada soal'], 404);
        }
        
        BankSoal::where('soal_id', $soal->id)->update(['nilai' => $request->nilai]);

        return response()->json([
            'success' => 'Semua nilai butir soal berhasil diperbarui!'
        ]);
    }
}
