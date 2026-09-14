<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\JadwalAudit;
use App\Models\ListPertanyaan;
use App\Models\Jawaban;
use App\Models\PenunjukanAuditor;
use App\Models\KonfigurasiNomorDokumen;
use App\Helper\PenugasanHelper;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Generate dokumen resmi Instrumen 1-6 (Check List, HAL, HAL-KS, HAL-KTS, PTK - Permintaan
 * Tindakan Koreksi, PTP - Permintaan Tindakan Peningkatan) sebagai PDF, dibangun dari 6 contoh
 * dokumen asli (DOCX) yang dikasih user + konfirmasi field-by-field lewat AskUserQuestion.
 *
 * **Instrumen 5 (PTK) - resolusi field "KATEGORI TEMUAN" (27 Agu):** di contoh dokumen, field ini
 * levelnya PER DOKUMEN (1 nilai per dokumen) padahal `jawabans.kategori_temuan` levelnya PER
 * BARIS/SOAL. User dapat kepastian dari pihak lain: dokumennya memang DIGENERATE PER KATEGORI -
 * jadi 1 jadwal bisa punya SAMPAI 3 dokumen Instrumen 5 terpisah (satu per OBS/MINOR/MAYOR),
 * masing-masing isi tabelnya CUMA baris KTS yang kategorinya PERSIS sama dengan kategori dokumen
 * itu. Makanya endpoint ini WAJIB terima query param `kategori_temuan` (salah satu OBS/MINOR/
 * MAYOR) khusus buat instrumen=5 - lihat filter $baris di bawah. Frontend (`CetakDokumen.vue`)
 * nawarin 3 tombol terpisah buat Instrumen 5, bukan 1.
 *
 * CATATAN PENTING soal field yang TIDAK ADA di database (dikonfirmasi user: "diisi manual pas
 * generate dokumen", zero migration):
 * - STANDAR (contoh: "Standar Mahasiswa") - kategori standar akreditasi, nggak ada kolomnya di
 *   bank_pertanyaans. User isi manual tiap kali generate lewat query param `standar`.
 * - TIPE AUDIT (contoh: "Reguler") - nggak ada kolomnya di jadwal_spmi. User isi manual lewat
 *   query param `tipe_audit`.
 * - DIVALIDASI (khusus Instrumen 5 & 6, contoh nama: "Cahyaningtyas Ria Uripi, S.E., M.Si.") -
 *   nama pihak ketiga yang memvalidasi dokumen (di luar Auditor/Auditee yang sudah ada datanya di
 *   `penunjukan_auditors`). Diisi manual lewat query param `divalidasi`, TIDAK disimpan ke DB.
 * Semuanya CUMA dipakai buat nge-print di header/footer dokumen, TIDAK disimpan ke database.
 *
 * CATATAN soal PERIODE AUDIT:
 * - PERIODE AUDIT: semester_spmi.semester formatnya "20272"/"20281" (5 digit). Saya asumsikan
 *   4 digit pertama = tahun ajaran mulai, digit terakhir = 1 (Ganjil) / 2 (Genap) - ini konvensi
 *   yang umum dipakai kampus di Indonesia. "20272" -> "Semester Genap 2027/2028". KALAU SALAH,
 *   gampang diperbaiki di formatPeriodeAudit() di bawah.
 *
 * NOMOR DOKUMEN (13 Sep 2026 - SEKARANG BISA DIATUR ADMIN): awalnya nomor dokumen Instrumen 1-6
 * hardcode di sini (array NOMOR_DOKUMEN_DEFAULT di bawah), sama buat semua jadwal & semua periode
 * selamanya. Client minta Admin bisa atur sendiri, DAN nomornya kemungkinan SAMA selama 1 PERIODE
 * (semester) - padahal 1 periode bisa punya BANYAK jadwal. Makanya konfigurasi disimpan PER
 * SEMESTER lewat tabel `konfigurasi_nomor_dokumen` (lihat KonfigurasiNomorDokumenController &
 * halaman Admin "Konfigurasi Dokumen") - Admin isi 1x per semester, otomatis berlaku ke SEMUA
 * jadwal yang semester-nya sama. Array NOMOR_DOKUMEN_DEFAULT di bawah TETAP DIPERTAHANKAN sebagai
 * FALLBACK - kalau Admin belum bikin konfigurasi buat semester tertentu (atau baru isi sebagian
 * dari 6 instrumen), instrumen yang belum diisi tetap jalan pakai nomor default ini, lihat
 * resolveNomorDokumen().
 */
class DokumenAuditController extends Controller
{
    private const NOMOR_DOKUMEN_DEFAULT = [
        1 => 'UNWIKU/SPMI/EVAL-AMI/CL.',
        2 => 'UNWIKU/SPMI/EVAL-AMI/HAL.A01',
        3 => 'UNWIKU/SPMI/EVAL-AMI/HAL-KS.A01',
        4 => 'UNWIKU/SPMI/EVAL-AMI/HAL-KTS.A01',
        // 5 = field NOMOR DOKUMEN kosong di contoh dokumen user, saya susun sendiri - lihat docblock.
        5 => 'UNWIKU/SPMI/EVAL-AMI/HAL-PTK.A01',
        // 6 = nomor ASLI dari contoh dokumen user (Instrumen 6), bukan tebakan - lihat docblock.
        6 => 'UNWIKU/SPMI/EVAL-AMI/HAL-PTP.A03',
    ];

    // Kategori temuan yang valid buat Instrumen 5 - PERSIS sama dengan pilihan di form penilaian
    // Auditor (NilaiInstrumenAuditor.vue), karena nilainya diambil langsung dari jawabans.kategori_temuan.
    private const KATEGORI_TEMUAN_VALID = ['OBS', 'MINOR', 'MAYOR'];
    private const KATEGORI_TEMUAN_LABEL = [
        'OBS' => 'Observasi (OBS)',
        'MINOR' => 'Minor',
        'MAYOR' => 'Mayor',
    ];

    private const INSTRUMEN_TERSEDIA = [1, 2, 3, 4, 5, 6];

    public function generate(Request $request, $jadwalId, $instrumen)
    {
        $instrumen = (int) $instrumen;

        if (!in_array($instrumen, self::INSTRUMEN_TERSEDIA, true)) {
            return response()->json([
                'error' => 'Instrumen ' . $instrumen . ' belum tersedia untuk dicetak.',
            ], 404);
        }

        // Kalau yang login akun Auditor (dosen), wajib ditugaskan di jadwal ini dulu baru boleh
        // cetak dokumennya - Admin lolos otomatis (lihat docblock PenugasanHelper). Endpoint ini
        // sendiri sudah dibatasi role admin|auditor di routes/api.php (lihat middleware 'claim').
        $errorPenugasan = PenugasanHelper::cekPenugasan($request, $jadwalId, 'auditor');
        if ($errorPenugasan) {
            return $errorPenugasan;
        }

        $request->validate([
            'standar'    => 'required|string|max:255',
            'tipe_audit' => 'required|string|max:255',
            // Cuma dipakai (dan diwajibkan) buat Instrumen 5 & 6 - lihat docblock soal field DIVALIDASI.
            'divalidasi' => in_array($instrumen, [5, 6], true) ? 'required|string|max:255' : 'nullable|string|max:255',
            // Cuma dipakai (dan diwajibkan) buat Instrumen 5 - dokumen digenerate PER KATEGORI,
            // lihat docblock soal resolusi field "KATEGORI TEMUAN".
            'kategori_temuan' => $instrumen === 5
                ? 'required|string|in:' . implode(',', self::KATEGORI_TEMUAN_VALID)
                : 'nullable|string',
        ]);

        $jadwal = JadwalAudit::find($jadwalId);
        if (!$jadwal) {
            return response()->json(['error' => 'Jadwal audit tidak ditemukan.'], 404);
        }

        $auditee = PenunjukanAuditor::with('dosen')
            ->where('jadwal_spmi_id', $jadwalId)
            ->where('status', 'auditee')
            ->first();

        // orderByDesc('is_ketua') (8 Sep) - Ketua Auditor (ditunjuk Admin di panel Penugasan
        // Auditor) selalu ditaruh PALING ATAS di sini. Ini yang dipakai buat 2 hal di bawah:
        // (1) auditorNama->first() jadi Ketua-nya (bukan lagi auditor pertama secara kebetulan
        //     dari urutan query) buat kolom tanda tangan DISUSUN/DISETUJUI di footer.
        // (2) daftar bernomor "AUDITOR" di header.blade.php otomatis nampilin Ketua di nomor 1.
        $auditors = PenunjukanAuditor::with('dosen')
            ->where('jadwal_spmi_id', $jadwalId)
            ->where('status', 'auditor')
            ->orderByDesc('is_ketua')
            ->get();

        // Sama seperti ListPertanyaanController::getByJadwal() - ambil soal + tempelkan jawaban
        // (kalau ada) berdasarkan list_pertanyaans.id (lihat catatan panjang di JawabanController).
        $listPertanyaan = ListPertanyaan::with('pertanyaan')
            ->where('jadwal_id', $jadwalId)
            ->get();

        $jawabanByListPertanyaanId = Jawaban::whereIn('pertanyaan_id', $listPertanyaan->pluck('id'))
            ->get()
            ->keyBy('pertanyaan_id');

        $listPertanyaan->each(function ($item) use ($jawabanByListPertanyaanId) {
            $item->jawaban = $jawabanByListPertanyaanId->get($item->id);
        });

        // Instrumen 1 & 2: semua soal di jadwal ini. Instrumen 3 & 6 (jalur KS): cuma yang
        // diputuskan KS. Instrumen 4 (jalur KTS): semua yang diputuskan KTS (semua kategori
        // sekaligus, ini rekap gabungan). Instrumen 5 (jalur KTS, PER KATEGORI): CUMA baris KTS
        // yang kategori_temuan-nya PERSIS sama dengan kategori yang diminta - beda dari Instrumen
        // 4 yang nampilin SEMUA kategori jadi satu dokumen.
        $kategoriTemuan = $request->input('kategori_temuan');
        $baris = match ($instrumen) {
            3, 6 => $listPertanyaan->filter(fn($i) => $i->jawaban?->status_temuan === 'KS')->values(),
            4 => $listPertanyaan->filter(fn($i) => $i->jawaban?->status_temuan === 'KTS')->values(),
            5 => $listPertanyaan->filter(
                fn($i) => $i->jawaban?->status_temuan === 'KTS'
                    && $i->jawaban?->kategori_temuan === $kategoriTemuan,
            )->values(),
            default => $listPertanyaan,
        };

        $data = [
            'jadwal'         => $jadwal,
            'standar'        => $request->input('standar'),
            'tipeAudit'      => $request->input('tipe_audit'),
            'divalidasi'     => $request->input('divalidasi'),
            'kategoriTemuan' => $instrumen === 5 ? (self::KATEGORI_TEMUAN_LABEL[$kategoriTemuan] ?? $kategoriTemuan) : null,
            'periodeAudit'   => $this->formatPeriodeAudit($jadwal->semester),
            'auditeeNama'    => $this->formatNamaDosen($auditee?->dosen),
            'auditorNama'    => $auditors->map(fn($a) => $this->formatNamaDosen($a->dosen, (bool) $a->is_ketua))->filter()->values(),
            'nomorDokumen'   => $this->resolveNomorDokumen($jadwal->semester, $instrumen),
            'baris'          => $baris,
        ];

        $namaFile = 'Instrumen-' . $instrumen
            . ($instrumen === 5 ? '-' . $kategoriTemuan : '')
            . '-' . $jadwal->nama_jadwal . '-' . $jadwal->area_audit . '.pdf';
        $namaFile = preg_replace('/[^A-Za-z0-9\-]+/', '-', $namaFile);

        return Pdf::loadView('dokumen.instrumen' . $instrumen, $data)
            ->setPaper('a4', 'portrait')
            ->download($namaFile);
    }

    /**
     * Cari nomor dokumen buat 1 instrumen, PER SEMESTER jadwal yang lagi diproses (lihat
     * docblock panjang soal NOMOR DOKUMEN di atas class ini). Kalau Admin belum bikin konfigurasi
     * sama sekali buat semester ini, ATAU sudah bikin tapi kolom instrumen yang diminta masih
     * kosong (belum sempat diisi), fallback ke NOMOR_DOKUMEN_DEFAULT - jadi generate dokumen
     * TIDAK PERNAH gagal/kosong gara-gara konfigurasi belum lengkap.
     */
    private function resolveNomorDokumen(?string $semester, int $instrumen): string
    {
        $default = self::NOMOR_DOKUMEN_DEFAULT[$instrumen];

        if (!$semester) {
            return $default;
        }

        $konfigurasi = KonfigurasiNomorDokumen::where('semester', $semester)->first();
        if (!$konfigurasi) {
            return $default;
        }

        $kolom = 'nomor_dokumen_' . $instrumen;
        return $konfigurasi->{$kolom} ?: $default;
    }

    /**
     * ASUMSI format kode semester (lihat catatan panjang di atas class ini) - 4 digit pertama =
     * tahun ajaran mulai, digit terakhir 1=Ganjil / 2=Genap. Kalau formatnya nggak cocok
     * (bukan 5 digit / digit terakhir bukan 1 atau 2), balikin kode aslinya apa adanya daripada
     * maksa nampilin teks yang salah.
     */
    private function formatPeriodeAudit(?string $semester): string
    {
        if (!$semester || strlen($semester) !== 5 || !ctype_digit($semester)) {
            return (string) $semester;
        }

        $tahun = (int) substr($semester, 0, 4);
        $tipe  = substr($semester, 4, 1);

        if ($tipe === '1') {
            return "Semester Ganjil {$tahun}/" . ($tahun + 1);
        }
        if ($tipe === '2') {
            return "Semester Genap {$tahun}/" . ($tahun + 1);
        }

        return $semester;
    }

    private function formatNamaDosen($dosen, bool $isKetua = false): ?string
    {
        if (!$dosen) {
            return null;
        }

        $nama = ucwords(strtolower($dosen->nama_dosen));
        $depan = trim((string) $dosen->gelar_depan);
        $belakang = trim((string) $dosen->gelar_belakang);

        $hasil = $depan ? $depan . ' ' . $nama : $nama;
        if ($belakang) {
            $hasil .= ', ' . $belakang;
        }

        // Suffix "(Ketua)" (8 Sep) - biar di daftar bernomor AUDITOR di header dokumen jelas
        // kelihatan siapa yang ditunjuk jadi Ketua (yang namanya juga dipakai di kolom tanda
        // tangan footer, lewat auditorNama->first() - lihat generate()).
        if ($isKetua) {
            $hasil .= ' (Ketua)';
        }

        return $hasil;
    }
}
