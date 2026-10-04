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

        // Fitur baru (30 Sep 2026, revisi jawaban), DIPERLUAS (1 Okt 2026): dulu ATURANNYA CUMA
        // SATU - sekali deskripsi_hasil keisi, terkunci permanen selamanya. Sempat direvisi jadi
        // "boleh revisi KALAU sudah ada temuan KTS" (gate status_temuan === 'KTS'). User sekarang
        // minta scope-nya diperluas lagi: Auditee boleh revisi jawaban utk SEMUA pertanyaan yang
        // SUDAH PERNAH DIJAWAB sebelumnya, apa pun status penilaiannya (belum dinilai sama
        // sekali/KS/KTS) - bukan cuma KTS lagi. Jadi SEKARANG TIDAK ADA LAGI gate status_temuan -
        // begitu deskripsi_hasil sudah pernah terisi, request baru ini otomatis dianggap REVISI
        // (tidak pernah ditolak lagi), bukan isian pertama.
        //
        // Catatan (4 Okt 2026) - kolom `direvisi_pada` AKTIF lagi (migration
        // add_direvisi_pada_to_jawabans_table): tiap revisi dicatat waktunya, ditampilkan ke
        // Auditor & Auditee sebagai info "Direvisi pada". Revisi TIDAK perlu diminta Auditor
        // (sengaja ditunda dulu atas permintaan user) - aturan 1 Okt tetap: semua soal yang
        // sudah dijawab boleh direvisi.
        $sedangRevisi = $jawaban && $jawaban->deskripsi_hasil;

        $deskripsiHasil = $request->jawaban . "\n\nLink Bukti Dokumen: " . $request->link_bukti;

        if ($jawaban) {
            if ($sedangRevisi) {
                // Buka lagi status_jawaban di list_pertanyaans supaya soal ini MUNCUL LAGI di
                // antrean "Siap Dinilai" milik Auditor - penilaian Auditor yang sudah ada (kalau
                // ada, bisa KS/KTS/masih sebagian) SENGAJA TIDAK dihapus di sini, tetap kebaca
                // Auditor sebagai konteks temuan sebelumnya sampai Auditor beneran submit ulang
                // penilaiannya (baru ketimpa, lihat store() di bawah).
                $listPertanyaan->update(['status_jawaban' => 'belum']);
                $jawaban->update([
                    'deskripsi_hasil' => $deskripsiHasil,
                    'direvisi_pada'   => Carbon::now(),
                ]);
            } else {
                $jawaban->update(['deskripsi_hasil' => $deskripsiHasil]);
            }
        } else {
            Jawaban::create([
                'pertanyaan_id'   => $listPertanyaan->id,
                'deskripsi_hasil' => $deskripsiHasil,
            ]);
        }

        return response()->json([
            'message' => 'Jawaban dan link bukti dokumen berhasil dikirim ke Auditor!',
        ], 201);
    }

    /**
     * TAHAP 2 (Auditor): baca jawaban Auditee, TULIS PENILAIAN sendiri atas jawaban itu
     * (`penilaian_auditor` - fitur baru 16 Sep 2026, diisi SEBELUM menentukan KS/KTS), lalu
     * putuskan KS/KTS dan isi field cabang. Hanya bisa dilakukan SETELAH Auditee mengisi
     * (deskripsi_hasil sudah terisi). `deskripsi_hasil` (jawaban asli Auditee) TIDAK ditimpa -
     * tetap punya Auditee, tetap dibaca Auditor sebagai konteks/dasar penilaian.
     *
     * `penilaian_auditor` inilah yang SEKARANG dicetak sebagai "Deskripsi Hasil Audit/Rumusan
     * Temuan Hasil AMI" di Instrumen 2,3,4,5,6 (dikonfirmasi user lewat AskUserQuestion) -
     * `deskripsi_hasil` (Jawaban Auditee) TIDAK LAGI dicetak di dokumen manapun, lihat
     * DokumenAuditController & resources/views/dokumen/instrumen{2,3,4,5,6}.blade.php.
     *
     * PENTING soal `rekomendasi`, `jadwal_penyelesaian`, `pihak_tanggung_jawab`: 3 kolom ini
     * DIPAKAI BERSAMA oleh jalur KS (Instrumen 6 - field (12)(14)(15) di diagram) DAN jalur KTS
     * (Instrumen 5 - field (8)(10)(11) di diagram). Bukan kolom terpisah per jalur, makanya
     * required TANPA syarat status_temuan (beda dengan kategori_temuan/faktor_penghambat/
     * rencana_perbaikan yang KTS-only, dan faktor_pendukung/rencana_peningkatan yang KS-only).
     */
    // Daftar kolom penilaian Auditor yang boleh disimpan satu-satu (per kolom) - dipakai di
    // store() buat filter field mana yang ADA di request. Satu tempat, biar nggak kececer kalau
    // nanti nambah kolom baru lagi.
    private const KOLOM_PENILAIAN = [
        'penilaian_auditor', 'status_temuan',
        'faktor_pendukung', 'rencana_peningkatan',
        'kategori_temuan', 'faktor_penghambat', 'rencana_perbaikan',
        'rekomendasi', 'jadwal_penyelesaian', 'pihak_tanggung_jawab',
    ];

    /**
     * TAHAP 2 (Auditor) - fitur baru (30 Sep 2026, "simpan per kolom"): dulu form ini SATU KALI
     * submit-semua-sekaligus (semua kolom `required`), dan begitu status_jawaban jadi 'sudah'
     * TERKUNCI PERMANEN - Auditor lain (1 jadwal bisa ditugaskan >1 Auditor) atau Auditor yang
     * sama nggak bisa lagi bantu lengkapi/revisi belakangan. Sekarang SEMUA kolom penilaian jadi
     * OPSIONAL per-request ('sometimes') - FE (NilaiInstrumenAuditor.vue) kirim SATU/BEBERAPA
     * kolom yang lagi di-"Simpan" user, bukan wajib semuanya sekaligus. Auditor manapun yang
     * ditugaskan di jadwal ini bisa mampir kapan saja & isi/edit kolom mana saja (kolaboratif,
     * dikonfirmasi user: "satu penilaian bersama, progress dilihat dari kolom mana yg terisi") -
     * gate lama "status_jawaban === sudah -> tolak" DIHAPUS, ganti dihitung ULANG otomatis di
     * akhir lewat hitungStatusLengkap() tiap kali ada yang disimpan (lihat di bawah), BUKAN
     * dikirim manual dari FE.
     */
    public function store(Request $request)
    {
        $request->validate([
            'jadwal_spmi_id' => 'required|exists:jadwal_spmi,id',
            'pertanyaan_id'  => 'required|exists:bank_pertanyaans,id',
            'penilaian_auditor'    => 'sometimes|nullable|string',
            'status_temuan'        => 'sometimes|nullable|in:KS,KTS',
            'rekomendasi'          => 'sometimes|nullable|string',
            // QOL (16 Sep 2026) - sekarang diisi lewat date picker di FE, divalidasi sebagai
            // tanggal beneran (dulu teks bebas, mis. "September 2026").
            'jadwal_penyelesaian'  => 'sometimes|nullable|date',
            'pihak_tanggung_jawab' => 'sometimes|nullable|string',
            'kategori_temuan'      => 'sometimes|nullable|in:OBS,MINOR,MAYOR',
            'faktor_penghambat'    => 'sometimes|nullable|string',
            'rencana_perbaikan'    => 'sometimes|nullable|string',
            'faktor_pendukung'     => 'sometimes|nullable|string',
            'rencana_peningkatan'  => 'sometimes|nullable|string',
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

        // Auditor cuma bisa menilai KALAU Auditee sudah mengisi jawabannya dulu (pertanyaan_id
        // di sini = list_pertanyaans.id, sama seperti storeAuditee() di atas)
        $jawaban = Jawaban::where('pertanyaan_id', $listPertanyaan->id)->first();

        if (!$jawaban || !$jawaban->deskripsi_hasil) {
            return response()->json(['error' => 'Auditee belum mengisi jawaban untuk pertanyaan ini, Auditor belum bisa menilai.'], 400);
        }

        // Cuma ambil kolom yang BENERAN dikirim di request ini (bukan collect(self::KOLOM_PENILAIAN)
        // polos, itu bakal nganggep kolom yang nggak dikirim = null dan nge-null-in kolom yang
        // sudah keisi dari simpanan Auditor lain/sebelumnya - $request->has() jaga field yang
        // memang nggak dikirim tetap dibiarkan apa adanya di DB).
        $dataUpdate = [];
        foreach (self::KOLOM_PENILAIAN as $kolom) {
            if ($request->has($kolom)) {
                $dataUpdate[$kolom] = $request->input($kolom);
            }
        }

        if (empty($dataUpdate)) {
            return response()->json(['error' => 'Tidak ada kolom yang dikirim untuk disimpan.'], 422);
        }

        DB::beginTransaction();
        try {
            // deskripsi_hasil SENGAJA tidak disentuh - itu punya Auditee.
            $jawaban->update($dataUpdate);
            $jawaban->refresh();

            $lengkap = $this->hitungStatusLengkap($jawaban);
            $listPertanyaan->update(['status_jawaban' => $lengkap ? 'sudah' : 'belum']);

            DB::commit();
            return response()->json([
                'message' => $lengkap
                    ? 'Kolom tersimpan - semua kolom wajib untuk jalur ini sudah lengkap!'
                    : 'Kolom berhasil disimpan.',
                'status_jawaban' => $lengkap ? 'sudah' : 'belum',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Terjadi kesalahan sistem saat menyimpan data', 'detail' => $e->getMessage()], 500);
        }
    }

    /**
     * Dipanggil tiap kali store() nyimpen kolom, buat mutusin status_jawaban 'sudah' vs 'belum'
     * sekarang (BUKAN kiriman manual dari FE - dulu status_jawaban 'sudah' diset langsung tanpa
     * cek kelengkapan beneran, karena dulu emang wajib semua field keisi sekaligus lewat
     * `required` di validasi. Sekarang validasinya 'sometimes', jadi kelengkapan HARUS dicek di
     * sini). Field yang dicek PERSIS sama dengan yang dulu `required`/`required_if` di store().
     */
    private function hitungStatusLengkap(Jawaban $jawaban): bool
    {
        if (!$jawaban->penilaian_auditor || !$jawaban->status_temuan) {
            return false;
        }

        // Dipakai bersama KS (Instrumen 6) & KTS (Instrumen 5).
        $kolomBersama = $jawaban->rekomendasi && $jawaban->jadwal_penyelesaian && $jawaban->pihak_tanggung_jawab;
        if (!$kolomBersama) {
            return false;
        }

        if ($jawaban->status_temuan === 'KS') {
            return (bool) ($jawaban->faktor_pendukung && $jawaban->rencana_peningkatan);
        }

        // KTS
        return (bool) ($jawaban->kategori_temuan && $jawaban->faktor_penghambat && $jawaban->rencana_perbaikan);
    }
}
