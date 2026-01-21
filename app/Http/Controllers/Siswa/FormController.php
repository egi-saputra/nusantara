<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

// Models
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Kejuruan;

class FormController extends Controller
{
    /**
     * Halaman register siswa
     */
    public function create()
    {
        return Inertia::render('Siswa/Form/Create', [
            'kelas'    => Kelas::select('id', 'kelas')->get(),
            'kejuruan' => Kejuruan::select('id', 'kejuruan')->get(),
        ]);
    }

    /**
     * Simpan data siswa
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],

            'nis'  => ['nullable', 'digits:10', 'unique:siswa,nis'],
            'nisn' => ['required', 'digits:10', 'unique:siswa,nisn'],

            'kelas_id'    => ['required'],
            'kejuruan_id' => ['required'],
        ]);

        Siswa::create([
            'nama_lengkap' => $request->nama_lengkap,
            'nis'          => $request->nis,
            'nisn'         => $request->nisn,
            'kelas_id'     => $request->kelas_id,
            'kejuruan_id'  => $request->kejuruan_id,

            // ID internal siswa (7 karakter)
            'id_siswa' => strtoupper(substr(uniqid(), 0, 7)),
            'status'   => 'Activated',

            // Tambahkan user_id
            'user_id'  => auth()->id(),
        ]);

        return redirect()
            ->route('siswa.dashboard')
            ->with('success', 'Data berhasil disimpan');
    }
}
