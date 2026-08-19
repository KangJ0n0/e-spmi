<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Jawaban;
use App\Models\ListPertanyaan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class JawabanController extends Controller
{
    public function store(Request $request)
    {
        // Validasi input dasar dari Vue
        $request->validate([
            'jadwal_spmi_id' => 'required|exists:jadwal_spmi,id',
            'pertanyaan_id'  => 'required|exists:bank_pertanyaans,id',
            'status_temuan'  => 'required|in:KS,KTS', // Sesuai/Tidak Sesuai
            'deskripsi_hasil'=> 'required|string',
            // Sisanya nullable tergantung KS/KTS
        ]);

        // SYARAT 1: Ngecek pertanyaan exist / ngga di list jadwal tersebut
        $listPertanyaan = ListPertanyaan::with('jadwalAudit')
            ->where('jadwal_id', $request->jadwal_spmi_id)
            ->where('pertanyaan_id', $request->pertanyaan_id)
            ->first();

        if (!$listPertanyaan) {
            return response()->json(['error' => 'Pertanyaan tidak ditemukan pada jadwal ini!'], 404);
        }

        // SYARAT 2: Cek tanggal pakai Carbon (waktu jadwal audit)
        $jadwal = $listPertanyaan->jadwalAudit;
        $now = Carbon::now();
        
        // Kita set awal hari (00:00:00) dan akhir hari (23:59:59)
        $tanggalMulai = Carbon::parse($jadwal->tanggal_awal)->startOfDay();
        $tanggalAkhir = Carbon::parse($jadwal->tanggal_akhir)->endOfDay();

        if ($now->lt($tanggalMulai) || $now->gt($tanggalAkhir)) {
            return response()->json([
                'error' => 'Gagal! Waktu pengisian audit sudah lewat atau belum dimulai (Batas: ' . $tanggalMulai->format('d M') . ' - ' . $tanggalAkhir->format('d M') . ')'
            ], 403);
        }

        // SYARAT 3: Cek status_jawaban kalau sudah 'sudah' kasih error
        if ($listPertanyaan->status_jawaban === 'sudah') {
            return response()->json(['error' => 'Pertanyaan ini sudah dijawab dan tidak bisa diisi ulang (status: sudah)!'], 400);
        }

        // SYARAT 4: Insert jawaban dan update status
        DB::beginTransaction();
        try {
            // Insert ke tabel jawabans
            Jawaban::create([
                'pertanyaan_id'        => $request->pertanyaan_id,
                // ID Jadwal juga bagus disimpan di sini (tambahkan ke migration jawabans jika perlu)
                'status_temuan'        => $request->status_temuan,
                'deskripsi_hasil'      => $request->deskripsi_hasil,
                'faktor_pendukung'     => $request->faktor_pendukung,
                'rencana_peningkatan'  => $request->rencana_peningkatan,
                'kategori_temuan'      => $request->kategori_temuan,
                'faktor_penghambat'    => $request->faktor_penghambat,
                'rekomendasi'          => $request->rekomendasi,
                'rencana_perbaikan'    => $request->rencana_perbaikan,
                'jadwal_penyelesaian'  => $request->jadwal_penyelesaian,
                'pihak_tanggung_jawab' => $request->pihak_tanggung_jawab,
            ]);

            // Update status_jawaban di list_pertanyaans menjadi 'sudah'
            $listPertanyaan->update(['status_jawaban' => 'sudah']);

            DB::commit();
            return response()->json(['message' => 'Jawaban berhasil disimpan dan status pertanyaan telah diupdate!'], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Terjadi kesalahan sistem saat menyimpan data', 'detail' => $e->getMessage()], 500);
        }
    }
}