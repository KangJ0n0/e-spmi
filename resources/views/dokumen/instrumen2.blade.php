<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Instrumen 2 - Hasil Audit Lapangan (HAL)</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        table { page-break-inside: auto; }
        tr { page-break-inside: avoid; page-break-after: auto; }
    </style>
</head>
<body>
    @php
        $judulDokumen = 'HASIL AUDIT LAPANGAN (HAL)';
        $auditeeFieldLabel = 'PELAKSANA STANDAR';
        $labelDokumen = 'DOKUMEN';
        // Kop surat (10 Sep 2026) - dicocokkan persis ke contoh dokumen asli Instrumen 2 (sama
        // persis dengan Instrumen 1).
        $kopSuratLpmu = true;
        $kopSuratBaris = [
            'Kampus Beji Karangsalam Purwokerto',
            'www.unwiku.ac.id',
            'Telp.(0281) 6439889, Fax. 6439711',
        ];
    @endphp
    @include('dokumen.partials.header')

    <table style="width:100%; border-collapse: collapse; font-size: 10.5px;">
        <thead>
            <tr>
                <th style="width:45%; border:1px solid #000; padding:5px; background:#f0f0f0;">Butir Pertanyaan</th>
                <th style="width:55%; border:1px solid #000; padding:5px; background:#f0f0f0;">
                    Deskripsi Hasil Audit / Rumusan Temuan Hasil AMI
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse($baris as $item)
                <tr>
                    <td style="border:1px solid #000; padding:5px; vertical-align:top; ">{!! format_teks_bernomor($item->pertanyaan?->butir_pertanyaan) !!}</td>
                    {{-- Ganti (16 Sep 2026): dicetak dari penilaian_auditor (rumusan temuan
                         Auditor), BUKAN lagi deskripsi_hasil (jawaban mentah Auditee) - lihat
                         catatan lengkap di JawabanController::store(). --}}
                    <td style="border:1px solid #000; padding:5px; vertical-align:top; ">{!! $item->jawaban?->penilaian_auditor ? format_teks_bernomor($item->jawaban?->penilaian_auditor) : '-' !!}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" style="border:1px solid #000; padding:10px; text-align:center;">
                        Belum ada soal pada jadwal ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @include('dokumen.partials.footer')
</body>
</html>
