<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ListPertanyaan;
use App\Models\Jawaban;
use Illuminate\Http\Request;

class ListPertanyaanController extends Controller
{
    // READ: Ambil soal berdasarkan jadwal_id dari tabel jadwal_spmi
    public function getByJadwal($jadwal_id)
    {
        $list = ListPertanyaan::with('pertanyaan')
            ->where('jadwal_id', $jadwal_id)
            ->latest()
            ->get();

        // Tempelkan baris jawabans yang cocok ke tiap item, biar Auditee/Auditor bisa lihat
        // deskripsi_hasil/status_temuan dst tanpa manggil endpoint terpisah. `jawaban` bernilai
        // null kalau memang belum ada yang isi sama sekali.
        //
        // PENTING: jawabans.pertanyaan_id di sini dicocokkan ke $item->id (list_pertanyaans.id),
        // BUKAN $item->pertanyaan_id (bank_pertanyaans.id) - supaya jawaban nggak ke-mix kalau
        // 1 soal dari bank dipakai ulang di jadwal lain. Lihat catatan lengkap di
        // JawabanController::cekJadwalDanTanggal().
        $jawabanByListPertanyaanId = Jawaban::whereIn('pertanyaan_id', $list->pluck('id'))
            ->get()
            ->keyBy('pertanyaan_id');

        $list->each(function ($item) use ($jawabanByListPertanyaanId) {
            $item->jawaban = $jawabanByListPertanyaanId->get($item->id);
        });

        return response()->json($list);
    }

    // CREATE: Masukkan soal dari Bank Pertanyaan ke Jadwal Audit
    public function store(Request $request)
    {
        $request->validate([
            'jadwal_id'        => 'required|exists:jadwal_spmi,id', // Cek keberadaan ID di tabel jadwal_spmi
            'pertanyaan_ids'   => 'required|array',
            'pertanyaan_ids.*' => 'exists:bank_pertanyaans,id'
        ]);

        $insertedData = [];

        foreach ($request->pertanyaan_ids as $pId) {
            $item = ListPertanyaan::firstOrCreate([
                'jadwal_id'     => $request->jadwal_id,
                'pertanyaan_id' => $pId,
            ]);
            $insertedData[] = $item;
        }

        return response()->json([
            'message' => 'Soal audit berhasil ditambahkan ke jadwal!',
            'data'    => $insertedData
        ], 201);
    }

    // DELETE: Copot soal dari jadwal
    public function destroy($id)
    {
        $list = ListPertanyaan::findOrFail($id);
        $list->delete();

        return response()->json(['message' => 'Soal berhasil dihapus dari jadwal!']);
    }
}