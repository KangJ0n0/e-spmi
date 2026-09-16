<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Instrumen 3 - Hasil Audit Lapangan Kesesuaian (HAL-KS)</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        table { page-break-inside: auto; }
        tr { page-break-inside: avoid; page-break-after: auto; }
    </style>
</head>
<body>
    @php
        $judulDokumen = 'HASIL AUDIT LAPANGAN KESESUAIAN (HAL-KS)';
        $auditeeFieldLabel = 'PELAKSANA STANDAR';
        $labelDokumen = 'NOMOR DOKUMEN';
        // Kop surat (10 Sep 2026) - dicocokkan persis ke contoh dokumen asli Instrumen 3 (beda
        // dari kop surat Instrumen 1&2 - tanpa baris "LEMBAGA PENJAMINAN MUTU", alamat lengkap).
        $kopSuratBaris = [
            'Jl. Beji Karangsalam No.25 Kec. Kedungbanteng. Purwokerto. Kab Banyumas. Jawa Tengah 53152',
            'Laman : www.unwiku.ac.id',
            'Email : unwiku@ac.id',
        ];
    @endphp
    @include('dokumen.partials.header')

    {{-- Instrumen 3 cuma nampilin soal yang diputuskan Auditor sebagai KS (Kondisi Sesuai) --}}
    <table style="width:100%; border-collapse: collapse; font-size: 10.5px;">
        <thead>
            <tr>
                <th style="width:30%; border:1px solid #000; padding:5px; background:#f0f0f0;">Check List</th>
                <th style="width:35%; border:1px solid #000; padding:5px; background:#f0f0f0;">Deskripsi Hasil Audit</th>
                <th style="width:35%; border:1px solid #000; padding:5px; background:#f0f0f0;">Faktor Pendukung Keberhasilan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($baris as $item)
                <tr>
                    <td style="border:1px solid #000; padding:5px; vertical-align:top; ">{!! format_teks_bernomor($item->pertanyaan?->butir_pertanyaan) !!}</td>
                    {{-- Ganti (16 Sep 2026): dicetak dari penilaian_auditor, bukan lagi
                         deskripsi_hasil - lihat JawabanController::store(). --}}
                    <td style="border:1px solid #000; padding:5px; vertical-align:top; ">{!! $item->jawaban?->penilaian_auditor ? format_teks_bernomor($item->jawaban?->penilaian_auditor) : '-' !!}</td>
                    <td style="border:1px solid #000; padding:5px; vertical-align:top; ">{!! $item->jawaban?->faktor_pendukung ? format_teks_bernomor($item->jawaban?->faktor_pendukung) : '-' !!}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="border:1px solid #000; padding:10px; text-align:center;">
                        Belum ada soal yang diputuskan Kondisi Sesuai (KS) pada jadwal ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @include('dokumen.partials.footer')
</body>
</html>
