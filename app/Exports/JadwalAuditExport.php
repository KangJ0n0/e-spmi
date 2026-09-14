<?php

namespace App\Exports;

use App\Models\JadwalAudit;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Export Jadwal Audit + Auditor/Auditee yang ditugaskan (13 Sep 2026) - fitur baru, dikonfirmasi
 * user ikutan nama Auditor/Auditee per jadwal (bukan cuma data jadwalnya doang), biar hasilnya
 * lebih lengkap dipakai buat laporan/arsip, bukan cuma daftar jadwal kosongan.
 *
 * 1 baris = 1 Jadwal Audit. Kolom Auditor/Auditee digabung jadi 1 sel per jadwal (dipisah koma)
 * karena 1 jadwal bisa punya BANYAK Auditor/Auditee sekaligus - sengaja TIDAK dipecah jadi 1 baris
 * per penugasan, soalnya itu bakal bikin nama jadwal/tanggal keulang-ulang tiap baris dan malah
 * lebih susah dibaca di Excel dibanding 1 baris ringkas per jadwal.
 */
class JadwalAuditExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        // Urutan: semester terbaru dulu, lalu tanggal mulai terbaru dulu - paling relevan buat
        // dilihat pertama kali pas dibuka di Excel (sama seperti urutan Konfigurasi Nomor Dokumen).
        return JadwalAudit::with('penunjukan.dosen')
            ->orderByDesc('semester')
            ->orderByDesc('tanggal_awal')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nama Jadwal',
            'Area Audit',
            'Semester / Periode',
            'Tanggal Awal',
            'Tanggal Akhir',
            'Auditor',
            'Auditee',
        ];
    }

    public function map($jadwal): array
    {
        // Ketua Auditor (kalau ada) ditaruh PALING DEPAN di sel-nya, sama seperti konvensi
        // "Ketua selalu di nomor 1" yang sudah dipakai di dokumen cetak (lihat
        // DokumenAuditController::generate(), orderByDesc('is_ketua')).
        $auditor = $jadwal->penunjukan
            ->where('status', 'auditor')
            ->sortByDesc('is_ketua')
            ->map(fn($p) => $p->dosen ? $p->dosen->nama_dosen . ($p->is_ketua ? ' (Ketua)' : '') : null)
            ->filter()
            ->implode(', ');

        $auditee = $jadwal->penunjukan
            ->where('status', 'auditee')
            ->map(fn($p) => $p->dosen?->nama_dosen)
            ->filter()
            ->implode(', ');

        return [
            $jadwal->nama_jadwal,
            $jadwal->area_audit,
            $this->formatSemester($jadwal->semester),
            $this->formatTanggal($jadwal->tanggal_awal),
            $this->formatTanggal($jadwal->tanggal_akhir),
            $auditor !== '' ? $auditor : '-',
            $auditee !== '' ? $auditee : '-',
        ];
    }

    // Sama persis konvensi format semester yang dipakai di
    // DokumenAuditController::formatPeriodeAudit() & KonfigurasiNomorDokumen.vue - 4 digit tahun
    // ajaran mulai + 1 digit tipe (1=Ganjil/2=Genap). Sengaja diduplikasi kecil di sini (bukan
    // ditarik ke 1 helper bersama) - biar file export ini tetap berdiri sendiri, gampang dibaca,
    // konsisten sama pola kelas Export lain di project ini (mis. BankPertanyaanExport.php).
    private function formatSemester(?string $semester): string
    {
        if (!$semester || strlen($semester) !== 5 || !ctype_digit($semester)) {
            return (string) $semester;
        }

        $tahun = (int) substr($semester, 0, 4);
        $tipe = substr($semester, 4, 1);

        if ($tipe === '1') {
            return "Ganjil {$tahun}/" . ($tahun + 1);
        }
        if ($tipe === '2') {
            return "Genap {$tahun}/" . ($tahun + 1);
        }
        return $semester;
    }

    private function formatTanggal($tanggal): string
    {
        if (!$tanggal) {
            return '-';
        }
        return Carbon::parse($tanggal)->translatedFormat('d F Y');
    }
}
