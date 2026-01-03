<?php

namespace App\Http\Controllers\Proktor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Exports\PesertaExport;
use App\Imports\PesertaImport;
use Maatwebsite\Excel\Facades\Excel;

class PesertaUjianController extends Controller
{
    public function index(Request $request)
    {
        $peserta = Siswa::with('kelas')
            ->orderBy('nama_lengkap')
            ->paginate(10)
            ->through(function ($s) {
                return [
                    'id'        => $s->id_siswa,
                    'nama'      => $s->nama_lengkap,
                    'kelas'     => $s->kelas->kelas ?? '-',
                    'kelas_id'  => $s->kelas_id,
                    'status'    => $s->status,
                ];
            });

        $pesertaAll = Siswa::with(['kelas', 'user'])
            ->orderBy('nama_lengkap')
            ->get()
            ->map(function ($s) {
                return [
                    'id'        => $s->id_siswa,
                    'nama'      => $s->nama_lengkap,
                    'kelas'     => $s->kelas->kelas ?? '-',
                    'kelas_id'  => $s->kelas_id,
                    'status'    => $s->status,
                    'email'     => $s->user->email ?? '',
                ];
            });

        $kelasList = DB::table('kelas')
            ->orderBy('kelas')
            ->get(['id', 'kelas']);

        return inertia('Proktor/Peserta/Index', [
            'peserta'    => $peserta,
            'pesertaAll' => $pesertaAll,
            'kelasList'  => $kelasList,
            'title'      => 'Users Directory'
        ]);
    }

    public function showForm()
    {
        $kelasList = DB::table('kelas')
            ->orderBy('kelas')
            ->get(['id', 'kelas']);

        $kejuruanList = DB::table('kejuruan')
            ->orderBy('kejuruan')
            ->get(['id', 'kejuruan']);

        return inertia('Proktor/Peserta/Register', [
            'kelasList'     => $kelasList,
            'kejuruanList'  => $kejuruanList,
            'title'         => 'Register User',
            'flash'         => session()->get('success')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email'        => 'required|email|max:255',
            'password'     => 'nullable|string|min:6',
            'kelas_id'     => 'required|exists:kelas,id',
            'kejuruan_id'  => 'required|exists:kejuruan,id',
        ]);

        // generate id_siswa unik
        do {
            $id_siswa = str_pad(rand(0, 9999999), 7, '0', STR_PAD_LEFT);
        } while (Siswa::where('id_siswa', $id_siswa)->exists());

        $user = User::create([
            'name'      => $request->nama_lengkap,
            'email'     => $request->email,
            'password'  => bcrypt($request->password ?? 'password'),
        ]);

        Siswa::create([
            'id_siswa'     => $id_siswa,
            'nama_lengkap' => $request->nama_lengkap,
            'kelas_id'     => $request->kelas_id,
            'kejuruan_id'  => $request->kejuruan_id,
            'status'       => 'Activated',
            'user_id'      => $user->id,
        ]);

        return response()->json([
            'success' => 'Peserta berhasil didaftarkan. Id Siswa: ' . $id_siswa
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'kelas_id'     => 'required',
            'status'       => 'required|in:Activated,Deactivated',
            'email'        => 'required|email|max:255',
            'password'     => 'nullable|string|min:6',
        ]);

        try {
            $peserta = Siswa::where('id_siswa', $id)->firstOrFail();

            $peserta->update([
                'nama_lengkap' => $request->nama_lengkap,
                'kelas_id'     => $request->kelas_id,
                'status'       => $request->status,
            ]);

            if ($peserta->user) {
                $peserta->user->update([
                    'email'    => $request->email,
                    'password' => $request->password ? bcrypt($request->password) : $peserta->user->password,
                ]);
            }

            return response()->json([
                'success' => 'Peserta berhasil diupdate.',
                'peserta' => [
                    'id'     => $peserta->id_siswa,
                    'nama'   => $peserta->nama_lengkap,
                    'kelas'  => $peserta->kelas->kelas ?? '-',
                    'status' => $peserta->status,
                    'email'  => $peserta->user->email ?? '',
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal update peserta: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new PesertaExport, 'template_peserta.xlsx');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'excel' => 'required|mimes:xlsx,xls',
        ]);

        $import = new PesertaImport();
        Excel::import($import, $request->file('excel'));

        return redirect()->route('proktor.peserta.index')
                        ->with('success', 'Data peserta berhasil diimport!');
    }

    public function destroy($id)
    {
        try {
            Siswa::where('id_siswa', $id)->delete();

            return response()->json([
                'success' => 'Peserta berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal hapus peserta: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyAll()
    {
        try {
            DB::table('siswa')->delete();
            DB::statement("ALTER TABLE siswa AUTO_INCREMENT = 1");

            return response()->json([
                'success' => 'Semua peserta berhasil dihapus dan ID di-reset.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal hapus semua peserta: ' . $e->getMessage()
            ], 500);
        }
    }
}
