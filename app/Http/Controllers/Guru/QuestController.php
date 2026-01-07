<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Soal;
use App\Models\BankSoal;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;
use App\Imports\BankSoalImport;
use App\Exports\BankSoalExport;
use Illuminate\Support\Facades\Storage;

class QuestController extends Controller
{
    public function index()
    {
        return redirect()->route('guru.soal.index');
    }


    public function create(Request $request)
    {
        return Inertia::render('Guru/Quest/Create', [
            // 'bankSoal' => $bankSoal,
            'soal_id' => $request->soal_id
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'soal_id' => 'required|exists:soal,id',
            'soal' => 'required|string',
            'tipe_soal' => 'required|in:PG,Essay',
            'jawaban_benar' => 'nullable|string',
            'nilai' => 'required|numeric',
            'jenis_lampiran' => 'nullable|string|in:Tanpa Lampiran,Gambar',
            'lampiran_file' => 'nullable|file|image|max:5120', // max 5MB
            'opsi_a' => 'nullable|string',
            'opsi_b' => 'nullable|string',
            'opsi_c' => 'nullable|string',
            'opsi_d' => 'nullable|string',
            'opsi_e' => 'nullable|string',
        ]);

        $linkLampiran = null;

        if ($request->jenis_lampiran === 'Gambar' && $request->hasFile('lampiran_file')) {
            $file = $request->file('lampiran_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/bank_soal', $filename);
            $linkLampiran = 'storage/bank_soal/' . $filename;
        }

        $bankSoal = BankSoal::create([
            'soal_id' => $request->soal_id,
            'soal' => $request->soal,
            'tipe_soal' => $request->tipe_soal,
            'jenis_lampiran' => $request->jenis_lampiran,
            'link_lampiran' => $linkLampiran,
            'jawaban_benar' => $request->jawaban_benar,
            'opsi_a' => $request->opsi_a,
            'opsi_b' => $request->opsi_b,
            'opsi_c' => $request->opsi_c,
            'opsi_d' => $request->opsi_d,
            'opsi_e' => $request->opsi_e,
            'nilai' => $request->nilai,
        ]);

        return response()->json([
            'success' => 'Your question has been successfully added!',
            'bankSoal' => $bankSoal,
        ]);
    }

    public function update(Request $request, BankSoal $bankSoal) 
    {
        // Cek apakah ada file lama
        $existingFile = $request->existing_file ?? null;

        // Validasi
        $request->validate([
            'soal' => 'required|string',
            'tipe_soal' => 'required|in:PG,Essay',
            'jawaban_benar' => 'nullable|string',
            'nilai' => 'nullable',
            'jenis_lampiran' => 'required|string|in:Tanpa Lampiran,Gambar',
            // jika jenis lampiran Gambar dan tidak ada file lama, lampiran_file wajib
            'lampiran_file' => $request->jenis_lampiran === 'Gambar' && !$existingFile
                ? 'required|file|image|max:5120'
                : '',
            'opsi_a' => 'nullable|string',
            'opsi_b' => 'nullable|string',
            'opsi_c' => 'nullable|string',
            'opsi_d' => 'nullable|string',
            'opsi_e' => 'nullable|string',
        ]);

        $linkLampiran = $bankSoal->link_lampiran; // default pakai file lama

        if ($request->jenis_lampiran === 'Gambar' && $request->hasFile('lampiran_file')) {
            // Hapus file lama jika ada
            if ($bankSoal->link_lampiran && \Storage::exists(str_replace('storage/', 'public/', $bankSoal->link_lampiran))) {
                \Storage::delete(str_replace('storage/', 'public/', $bankSoal->link_lampiran));
            }

            // Simpan file baru
            $file = $request->file('lampiran_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/bank_soal', $filename);
            $linkLampiran = 'storage/bank_soal/' . $filename;
        } elseif ($request->jenis_lampiran === 'Tanpa Lampiran') {
            $linkLampiran = null; // hapus lampiran jika sebelumnya ada
        }

        $bankSoal->update([
            'soal' => $request->soal,
            'tipe_soal' => $request->tipe_soal,
            'jenis_lampiran' => $request->jenis_lampiran,
            'link_lampiran' => $linkLampiran,
            'jawaban_benar' => $request->jawaban_benar,
            'opsi_a' => $request->opsi_a,
            'opsi_b' => $request->opsi_b,
            'opsi_c' => $request->opsi_c,
            'opsi_d' => $request->opsi_d,
            'opsi_e' => $request->opsi_e,
            'nilai' => $request->nilai,
        ]);

        return response()->json([
            'success' => 'Question has been successfully updated!',
            'bankSoal' => $bankSoal,
        ]);
    }

    public function edit(BankSoal $bankSoal)
    {
        return Inertia::render('Guru/Quest/Edit', [
            'bankSoal' => $bankSoal,
            'soal' => $bankSoal,
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'excel' => 'required|file|mimes:xlsx,xls',
            'soal_id' => 'required|exists:soal,id',
        ]);

        $file = $request->file('excel');
        Excel::import(new \App\Imports\BankSoalImport($request->soal_id), $file);

        // Return JSON dengan pesan dan URL redirect
        return response()->json([
            'success' => 'Your questions has been successfully imported!',
            'redirect' => route('guru.soal.show', ['soal' => $request->soal_id]),
        ]);
    }

    public function downloadTemplate()
    {
        return Excel::download(new BankSoalExport, 'template_bank_soal.xlsx');
    }

    public function destroy(BankSoal $bankSoal)
    {
        // Hapus file fisik jika ada
        if ($bankSoal->link_lampiran && Storage::exists(str_replace('storage/', 'public/', $bankSoal->link_lampiran))) {
            Storage::delete(str_replace('storage/', 'public/', $bankSoal->link_lampiran));
        }

        $bankSoal->delete();

        return response()->json([
            'success' => 'This question has been successfully deleted!',
            'id' => $bankSoal->id,
        ]);
    }

    public function destroyAll($soal_id)
    {
        $soal = Soal::findOrFail($soal_id);

        // Hapus file fisik tiap soal jika ada
        foreach ($soal->bank_soal as $bankSoal) {
            if ($bankSoal->link_lampiran && Storage::exists(str_replace('storage/', 'public/', $bankSoal->link_lampiran))) {
                Storage::delete(str_replace('storage/', 'public/', $bankSoal->link_lampiran));
            }
        }

        // Hapus record database
        $soal->bank_soal()->delete();

        return response()->json([
            'success' => 'All questions have been successfully deleted!',
            'soal_id' => $soal_id,
        ]);
    }
}
