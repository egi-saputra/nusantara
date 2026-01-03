<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PengumumanController extends Controller
{
    public function index(Request $request)
    {
        $query = Pengumuman::query();

        $user = $request->user();

        $query->where(function($q) use ($request, $user) {
            if ($request->has('id')) {
                $q->where('id', $request->id);
            }
            $q->where(function($sub) use ($user) {
                $sub->where('penerima', 'semua')
                    ->orWhere('penerima', $user->role)
                    ->orWhereNull('penerima'); // optional
            });
        });

        $pengumuman = $query->with('kelas', 'user')->get();

        return Inertia::render('Pengumuman/Index', [
            'announcements' => $pengumuman
        ]);
    }

    public function create()
    {
        $kelas = Kelas::orderBy('kelas')->get();

        return Inertia::render('Pengumuman/Create', [
            'kelas' => $kelas
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'pengumuman' => 'required',
            'penerima' => 'required',
            'kelas_id' => 'nullable|exists:kelas,id',
        ]);

        // Simpan hasil create ke variabel
        $pengumuman = Pengumuman::create([
            'judul' => $request->judul,
            'pengumuman' => $request->pengumuman,
            'penerima' => strtolower($request->penerima),
            'kelas_id' => $request->kelas_id,
            'user_id' => auth()->id(),
        ]);

        // Kembali ke halaman sebelumnya dengan flash message & id
        return back()->with('success', 'Pengumuman berhasil dibuat!')->with('id', $pengumuman->id);
    }

    public function destroy(Pengumuman $pengumuman)
    {
        // pastikan hanya user yang membuatnya yang bisa hapus
        if ($pengumuman->user_id !== auth()->id()) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }

        $pengumuman->delete();

        return response()->json(['message' => 'Message has been deleted successfully']);
    }

    public function deleteAll(Request $request)
    {
        $userId = $request->user()->id;
        Pengumuman::where('user_id', $userId)->delete();
        return response()->json(['message' => 'All messages has been deleted successfully']);
    }

}
