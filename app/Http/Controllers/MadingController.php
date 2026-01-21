<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MadingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Pengumuman::query();

        // Jika ada filter ID
        if ($request->has('id')) {
            $query->where('id', $request->id);
        }

        // Hanya tampilkan pengumuman untuk semua (public)
        $query->where('penerima', 'semua');

        $pengumuman = $query->with(['kelas', 'user'])->get();

        return Inertia::render('Mading/Index', [
            'announcements' => $pengumuman,
            'auth' => [
                'user' => $user,
                'role' => $user?->role,
            ],
        ]);
    }

    public function show($id)
    {
        $announcement = Pengumuman::findOrFail($id);

        return Inertia::render('Mading/Show', [
            'announcement' => $announcement
        ]);
    }

}
