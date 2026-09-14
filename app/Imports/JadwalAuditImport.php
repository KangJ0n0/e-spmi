<?php

namespace App\Imports;

use App\Models\JadwalAudit;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Import Jadwal Audit dari Excel (13 Sep 2026) - Admin upload 1 file Excel berisi banyak baris
 * Jadwal Audit sekaligus, dibanding input manual satu-satu lewat form Tambah Jadwal. Pola class
 * ini (ToCollection + WithHeadingRow, $importedCount/$skipped/$totalBaris) SAMA PERSIS dengan
 * BankPertanyaanImport.php yang sudah ada, biar konsisten & Admin dapat pengalaman yang sama
 * (lihat JadwalAuditController::importExcel() buat cara hasilnya dikirim balik ke frontend).
 *
 * Kolom yang WAJIB ada di baris judul (baris pertama): nama_jadwal, area_audit, tanggal_awal,
 * tanggal_akhir, semester. TIDAK ada kolom Auditor/Auditee di sini SENGAJA - penugasan
 * Auditor/Auditee tetap lewat panel "Auditor/Auditee" yang sudah ada (butuh pilih dosen dari
 * tabel `dosen`, bukan sekadar teks nama - beda proses dari sekadar bikin baris Jadwal Audit).
 *
 * Baris yang nama_jadwal + area_audit + semester-nya SAMA PERSIS dengan yang sudah ada di
 * database (atau sudah diproses di baris SEBELUMNYA di file yang sama) DILEWATI (bukan dianggap
 * error) - dikonfirmasi user: "Lewati baris itu, lanjut ke baris lain".
 */
class JadwalAuditImport implements ToCollection, WithHeadingRow
{
    public $importedCount = 0;

    // Sama seperti BankPertanyaanImport::$skipped - tiap entri: ['baris' => nomor baris asli di
    // Excel, 'tipe' => 'info' (dilewati sengaja, bukan masalah) / 'peringatan' (kemungkinan data
    // salah, perlu dicek manual), 'alasan' => teks penjelasan].
    public array $skipped = [];

    public int $totalBaris = 0;

    public function collection(Collection $rows)
    {
        $this->totalBaris = $rows->count();

        // Kombinasi nama_jadwal|area_audit|semester yang SUDAH ada di database - dicek SEKALI
        // di awal (bukan query berulang per baris) biar import banyak baris tetap cepat.
        $kombinasiSudahAda = JadwalAudit::query()
            ->get(['nama_jadwal', 'area_audit', 'semester'])
            ->map(fn($j) => $this->kunciKombinasi($j->nama_jadwal, $j->area_audit, $j->semester))
            ->flip();

        foreach ($rows as $rowIndex => $row) {
            // headingRow() default = baris 1, jadi $rows di sini sudah TIDAK termasuk baris
            // judul dan 0-indexed mulai dari baris data pertama -> baris Excel asli = index + 2.
            $baris = $rowIndex + 2;

            $namaJadwal   = trim((string) ($row['nama_jadwal'] ?? ''));
            $areaAudit    = trim((string) ($row['area_audit'] ?? ''));
            $semester     = trim((string) ($row['semester'] ?? ''));
            $tanggalAwalRaw   = $row['tanggal_awal'] ?? null;
            $tanggalAkhirRaw  = $row['tanggal_akhir'] ?? null;

            // Baris benar-benar kosong semua kolom (spacer/jarak di Excel) - dilewati diam-diam,
            // TIDAK dilaporkan (bukan anomali, memang tidak ada apa-apa di baris ini). Sama pola
            // dengan BankPertanyaanImport.
            if ($namaJadwal === '' && $areaAudit === '' && $semester === '' && empty($tanggalAwalRaw) && empty($tanggalAkhirRaw)) {
                continue;
            }

            // Validasi field wajib (sama seperti JadwalAuditController::store()).
            if ($namaJadwal === '' || $areaAudit === '' || $semester === '') {
                $this->skipped[] = [
                    'baris'  => $baris,
                    'tipe'   => 'peringatan',
                    'alasan' => 'Kolom nama_jadwal, area_audit, atau semester kosong - baris ini dilewati.',
                ];
                continue;
            }

            $tanggalAwal = $this->parseTanggal($tanggalAwalRaw);
            $tanggalAkhir = $this->parseTanggal($tanggalAkhirRaw);

            if (!$tanggalAwal || !$tanggalAkhir) {
                $this->skipped[] = [
                    'baris'  => $baris,
                    'tipe'   => 'peringatan',
                    'alasan' => 'Kolom tanggal_awal atau tanggal_akhir tidak terbaca sebagai tanggal yang valid - baris ini dilewati.',
                ];
                continue;
            }

            if (Carbon::parse($tanggalAkhir)->lt(Carbon::parse($tanggalAwal))) {
                $this->skipped[] = [
                    'baris'  => $baris,
                    'tipe'   => 'peringatan',
                    'alasan' => 'Tanggal Akhir lebih awal dari Tanggal Awal - baris ini dilewati.',
                ];
                continue;
            }

            $kunci = $this->kunciKombinasi($namaJadwal, $areaAudit, $semester);
            if (isset($kombinasiSudahAda[$kunci])) {
                $this->skipped[] = [
                    'baris'  => $baris,
                    'tipe'   => 'info',
                    'alasan' => "Jadwal \"" . Str::limit($namaJadwal, 60) . "\" - {$areaAudit} - semester {$semester} sudah ada, baris ini dilewati (tidak dibuat ulang).",
                ];
                continue;
            }

            JadwalAudit::create([
                'nama_jadwal'   => $namaJadwal,
                'area_audit'    => $areaAudit,
                'tanggal_awal'  => $tanggalAwal,
                'tanggal_akhir' => $tanggalAkhir,
                'semester'      => $semester,
            ]);
            $this->importedCount++;

            // Tandai kombinasi ini "sudah ada" juga buat sisa baris di file yang sama - jaga-jaga
            // kalau Admin tanpa sengaja punya baris duplikat DI DALAM 1 file yang sama.
            $kombinasiSudahAda[$kunci] = true;
        }
    }

    private function kunciKombinasi(string $namaJadwal, string $areaAudit, string $semester): string
    {
        return mb_strtolower(trim($namaJadwal)) . '|' . mb_strtolower(trim($areaAudit)) . '|' . trim($semester);
    }

    // Excel bisa ngirim tanggal dalam 3 bentuk tergantung format sel di file aslinya: object
    // tanggal (DateTime, kalau selnya diformat sebagai Date di Excel), angka serial Excel (kalau
    // Excel-nya nyimpen tanggal sebagai angka mentah), atau teks biasa ("2026-09-01", "1 Sep
    // 2026", dst). Dicoba ketiganya biar Admin nggak perlu tahu persis format mana yang harus
    // dipakai - selama masih bisa "dibaca" sebagai tanggal, tetap diterima.
    private function parseTanggal($value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->format('Y-m-d');
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        if (is_string($value) && trim($value) !== '') {
            try {
                return Carbon::parse(trim($value))->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }
}
