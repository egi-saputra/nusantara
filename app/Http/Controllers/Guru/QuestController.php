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
            'jenis_lampiran' => 'nullable|string',
            'link_lampiran' => 'nullable|string',
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
        } elseif ($request->jenis_lampiran === 'Video') {
            $linkLampiran = $request->link_lampiran;
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
        // Validasi dasar
        $request->validate([
            'soal' => 'required|string',
            'tipe_soal' => 'required|in:PG,Essay',
            'jawaban_benar' => 'nullable|string',
            'nilai' => 'nullable', // tidak wajib dan tidak divalidasi
            'jenis_lampiran' => 'required|string|in:Tanpa Lampiran,Gambar,Video',
            'link_lampiran' => $request->jenis_lampiran === 'Video' ? 'required|string' : 'nullable|string',
            'lampiran_file' => $request->jenis_lampiran === 'Gambar' ? 'required|file|image|max:5120' : 'nullable',
            'opsi_a' => 'nullable|string',
            'opsi_b' => 'nullable|string',
            'opsi_c' => 'nullable|string',
            'opsi_d' => 'nullable|string',
            'opsi_e' => 'nullable|string',
        ]);

        $linkLampiran = $bankSoal->link_lampiran;

        if ($request->jenis_lampiran === 'Gambar' && $request->hasFile('lampiran_file')) {
            $file = $request->file('lampiran_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/bank_soal', $filename);
            $linkLampiran = 'storage/bank_soal/' . $filename;
        } elseif ($request->jenis_lampiran === 'Video') {
            $linkLampiran = $request->link_lampiran;
        } elseif ($request->jenis_lampiran === 'Tanpa Lampiran') {
            $linkLampiran = null;
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
        $bankSoal->delete();

        return response()->json([
            'success' => 'This question has been successfully deleted!',
            'id' => $bankSoal->id,
        ]);
    }

    public function destroyAll($soal_id)
    {
        $soal = Soal::findOrFail($soal_id);
        $soal->bank_soal()->delete();

        return response()->json([
            'success' => 'All questions have been successfully deleted!',
            'soal_id' => $soal_id,
        ]);
    }
}
