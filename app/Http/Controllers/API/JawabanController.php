<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Jawaban;
use App\Models\ListPertanyaan;
use App\Helper\PenugasanHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class JawabanController extends Controller
{
    /**
     * Cek pertanyaan ada di list_pertanyaans utk jadwal ini + cek rentang tanggal jadwal
     * (Carbon). Dipakai oleh storeAuditee() dan store() - dua-duanya butuh 2 syarat yang sama
     * sebelum lanjut ke syarat masing-masing. jadwal_spmi_id di sini CUMA dipakai buat validasi
     * (nggak disimpan di tabel jawabans - tabel ini persis sesuai 11 kolom dari spek chat WA,
     * nggak ada kolom tambahan).
     *
     * PENTING soal `jawabans.pertanyaan_id`: kolom ini SEKARANG diisi dengan `list_pertanyaans.id`
     * ($listPertanyaan->id), BUKAN `bank_pertanyaans.id` langsung. Alasannya: 1 soal di bank bisa
     * dipakai ulang di banyak jadwal (itu tujuan bank pertanyaan - reusable). Kalau jawabans
     * nunjuk ke bank_pertanyaans.id langsung, jawaban di jadwal semester ganjil bisa ke-mix
     * sama jadwal semester genap buat soal yang sama. `list_pertanyaans.id` unik per
     * (jadwal, soal), jadi itu yang dipakai. Nama kolom TETAP "pertanyaan_id" (nggak di-rename,
     * zero migration) - JANGAN bingung baca ini sebagai bank_pertanyaans.id.
     *
     * Return [ListPertanyaan, null] kalau lolos, atau [null, JsonResponse] kalau gagal.
     */
    private function cekJadwalDanTanggal($jadwalId, $pertanyaanId)
    {
        $listPertanyaan = ListPertanyaan::with('jadwalAudit')
            ->where('jadwal_id', $jadwalId)
            ->where('pertanyaan_id', $pertanyaanId)
            ->first();

        if (!$listPertanyaan) {
            return [null, response()->json(['error' => 'Pertanyaan tidak ditemukan pada jadwal ini!'], 404)];
        }

        $jadwal = $listPertanyaan->jadwalAudit;
        $now = Carbon::now();
        $tanggalMulai = Carbon::parse($jadwal->tanggal_awal)->startOfDay();
        $tanggalAkhir = Carbon::parse($jadwal->tanggal_akhir)->endOfDay();

        if ($now->lt($tanggalMulai) || $now->gt($tanggalAkhir)) {
            return [null, response()->json([
                'error' => 'Gagal! Waktu pengisian audit sudah lewat atau belum dimulai (Batas: ' . $tanggalMulai->format('d M') . ' - ' . $tanggalAkhir->format('d M') . ')'
            ], 403)];
        }

        return [$listPertanyaan, null];
    }

    /**
     * TAHAP 1 (Auditee): isi jawaban + link bukti dokumen (Google Drive). Sekali isi, selesai -
     * tidak bisa diisi ulang.
     *
     * `jawaban` dan `link_bukti` dikirim sebagai 2 field TERPISAH dari form (biar UX-nya jelas -
     * ada input teks jawaban + input url link Gdrive sendiri-sendiri), tapi DISIMPAN GABUNG jadi
     * satu string di kolom `deskripsi_hasil` yang sudah ada (field (4) di diagram alur) - TIDAK
     * ADA kolom baru di database buat link_bukti. Auditor baca keduanya sekaligus lewat
     * deskripsi_hasil yang sudah digabung ini.
     */
    public function storeAuditee(Request $request)
    {
        $request->validate([
            'jadwal_spmi_id' => 'required|exists:jadwal_spmi,id',
            'pertanyaan_id'  => 'required|exists:bank_pertanyaans,id',
            'jawaban'        => 'required|string',
            'link_bukti'     => 'required|url',
        ]);

        // Cek Auditee yang login BENERAN ditugaskan di jadwal ini (bukan cuma jadwal ada &
        // tanggal cocok, yang dicek cekJadwalDanTanggal() di bawah) - lihat docblock PenugasanHelper.
        $errorPenugasan = PenugasanHelper::cekPenugasan($request, $request->jadwal_spmi_id, 'auditee');
        if ($errorPenugasan) {
            return $errorPenugasan;
        }

        [$listPertanyaan, $errorResponse] = $this->cekJadwalDanTanggal($request->jadwal_spmi_id, $request->pertanyaan_id);
        if ($errorResponse) {
            return $errorResponse;
        }

        // pertanyaan_id di jawabans = list_pertanyaans.id (lihat catatan di cekJadwalDanTanggal),
        // BUKAN $request->pertanyaan_id (yang itu bank_pertanyaans.id, cuma dipakai buat cari
        // $listPertanyaan di atas).
        $jawaban = Jawaban::where('pertanyaan_id', $listPertanyaan->id)->first();

        if ($jawaban && $jawaban->deskripsi_hasil) {
            return response()->json(['error' => 'Pertanyaan ini sudah pernah dijawab dan tidak bisa diisi ulang!'], 400);
        }

        $deskripsiHasil = $request->jawaban . "\n\nLink Bukti Dokumen: " . $request->link_bukti;

        if ($jawaban) {
            $jawaban->update(['deskripsi_hasil' => $deskripsiHasil]);
        } else {
            Jawaban::create([
                'pertanyaan_id'   => $listPertanyaan->id,
                'deskripsi_hasil' => $deskripsiHasil,
            ]);
        }

        return response()->json(['message' => 'Jawaban dan link bukti dokumen berhasil dikirim ke Auditor!'], 201);
    }

    /**
     * TAHAP 2 (Auditor): baca jawaban Auditee, putuskan KS/KTS, isi field cabang.
     * Hanya bisa dilakukan SETELAH Auditee mengisi (deskripsi_hasil sudah terisi).
     * TIDAK menimpa deskripsi_hasil - itu tetap punya Auditee.
     *
     * PENTING soal `rekomendasi`, `jadwal_penyelesaian`, `pihak_tanggung_jawab`: 3 kolom ini
     * DIPAKAI BERSAMA oleh jalur KS (Instrumen 6 - field (12)(14)(15) di diagram) DAN jalur KTS
     * (Instrumen 5 - field (8)(10)(11) di diagram). Bukan kolom terpisah per jalur, makanya
     * required TANPA syarat status_temuan (beda dengan kategori_temuan/faktor_penghambat/
     * rencana_perbaikan yang KTS-only, dan faktor_pendukung/rencana_peningkatan yang KS-only).
     */
    public function store(Request $request)
    {
        $request->validate([
            'jadwal_spmi_id' => 'required|exists:jadwal_spmi,id',
            'pertanyaan_id'  => 'required|exists:bank_pertanyaans,id',
            'status_temuan'  => 'required|in:KS,KTS',
            // Dipakai bersama KS (Instrumen 6) & KTS (Instrumen 5) - lihat catatan di atas.
            'rekomendasi'          => 'required|string',
            'jadwal_penyelesaian'  => 'required|string',
            'pihak_tanggung_jawab' => 'required|string',
            // Khusus jalur KTS (Instrumen 4 & 5).
            'kategori_temuan'      => 'required_if:status_temuan,KTS|nullable|in:OBS,MINOR,MAYOR',
            'faktor_penghambat'    => 'required_if:status_temuan,KTS|nullable|string',
            'rencana_perbaikan'    => 'required_if:status_temuan,KTS|nullable|string',
            // Khusus jalur KS (Instrumen 3 & 6).
            'faktor_pendukung'     => 'required_if:status_temuan,KS|nullable|string',
            'rencana_peningkatan'  => 'required_if:status_temuan,KS|nullable|string',
        ]);

        // Cek Auditor yang login BENERAN ditugaskan di jadwal ini - lihat docblock PenugasanHelper.
        $errorPenugasan = PenugasanHelper::cekPenugasan($request, $request->jadwal_spmi_id, 'auditor');
        if ($errorPenugasan) {
            return $errorPenugasan;
        }

        [$listPertanyaan, $errorResponse] = $this->cekJadwalDanTanggal($request->jadwal_spmi_id, $request->pertanyaan_id);
        if ($errorResponse) {
            return $errorResponse;
        }

        // SYARAT: Cek status_jawaban kalau sudah 'sudah' kasih error (sudah pernah dinilai)
        if ($listPertanyaan->status_jawaban === 'sudah') {
            return response()->json(['error' => 'Pertanyaan ini sudah dinilai dan tidak bisa diisi ulang (status: sudah)!'], 400);
        }

        // SYARAT BARU: Auditor cuma bisa menilai KALAU Auditee sudah mengisi jawabannya dulu
        // (pertanyaan_id di sini = list_pertanyaans.id, sama seperti storeAuditee() di atas)
        $jawaban = Jawaban::where('pertanyaan_id', $listPertanyaan->id)->first();

        if (!$jawaban || !$jawaban->deskripsi_hasil) {
            return response()->json(['error' => 'Auditee belum mengisi jawaban untuk pertanyaan ini, Auditor belum bisa menilai.'], 400);
        }

        DB::beginTransaction();
        try {
            // UPDATE baris jawaban yang sudah dibuat Auditee (bukan insert baru).
            // deskripsi_hasil SENGAJA tidak disentuh - itu tetap jawaban asli Auditee.
            $jawaban->update([
                'status_temuan'        => $request->status_temuan,
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
            return response()->json(['message' => 'Jawaban berhasil dinilai dan status pertanyaan telah diupdate!'], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Terjadi kesalahan sistem saat menyimpan data', 'detail' => $e->getMessage()], 500);
        }
    }
}
