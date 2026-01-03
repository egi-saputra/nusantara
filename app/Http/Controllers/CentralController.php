<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CentralController extends Controller
{
    // Halaman login Inertia (Vue)
    public function showLoginForm()
    {
        return inertia('Auth/Central');
    }

    // Proses login
    public function central(Request $request)
    {
        // Validasi: wajib 7 digit angka
        $request->validate([
            'kode' => ['required', 'digits:7'],
        ]);

        $kode = $request->kode;

        // Cek apakah no_peserta ada di tabel peserta_ujian
        $peserta = DB::table('peserta_ujian')
            ->where('no_peserta', $kode)
            ->first();

        if (!$peserta) {
            return back()->withErrors([
                'kode' => 'Nomor peserta tidak ditemukan.',
            ]);
        }

        // Login manual menggunakan Auth::login() (tanpa password)
        // Pastikan model User ada dan bisa dibuatkan akun login otomatis
        $user = \App\Models\User::firstOrCreate(
            ['kode' => $kode],
            ['name' => $peserta->nama ?? 'Peserta ' . $kode]
        );

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }
}
