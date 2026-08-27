<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Instrumen 5 - Permintaan Tindakan Koreksi (PTK)</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        table { page-break-inside: auto; }
        tr { page-break-inside: avoid; page-break-after: auto; }
    </style>
</head>
<body>
    @php
        $judulDokumen = 'PERMINTAAN TINDAKAN KOREKSI (PTK)';
        $auditeeFieldLabel = 'PELAKSANA STANDAR';
        $labelDokumen = 'NOMOR DOKUMEN';
        $labelPeriode = 'PERIODE AUDIT MUTU INTERNAL';
    @endphp
    @include('dokumen.partials.header')

    {{-- Instrumen 5 (jalur KTS - Tindak Lanjut) CUMA nampilin soal yang: (1) diputuskan KTS, DAN
         (2) kategori_temuan-nya PERSIS sama dengan $kategoriTemuan di header - dokumen ini
         digenerate PER KATEGORI (bisa sampai 3 dokumen terpisah per jadwal: OBS/MINOR/MAYOR),
         beda dari Instrumen 4 yang nampilin SEMUA kategori jadi satu rekap. --}}
    <table style="width:100%; border-collapse: collapse; font-size: 9.5px;">
        <thead>
            <tr>
                <th style="width:5%; border:1px solid #000; padding:4px; background:#f0f0f0;">No</th>
                <th style="width:20%; border:1px solid #000; padding:4px; background:#f0f0f0;">Deskripsi Temuan Audit</th>
                <th style="width:16%; border:1px solid #000; padding:4px; background:#f0f0f0;">Akar Penyebab / Faktor Penghambat</th>
                <th style="width:14%; border:1px solid #000; padding:4px; background:#f0f0f0;">Rekomendasi</th>
                <th style="width:15%; border:1px solid #000; padding:4px; background:#f0f0f0;">Rencana Perbaikan</th>
                <th style="width:15%; border:1px solid #000; padding:4px; background:#f0f0f0;">Jadwal Penyelesaian</th>
                <th style="width:15%; border:1px solid #000; padding:4px; background:#f0f0f0;">Pihak Bertanggungjawab</th>
            </tr>
        </thead>
        <tbody>
            @forelse($baris as $index => $item)
                <tr>
                    <td style="border:1px solid #000; padding:4px; text-align:center; vertical-align:top;">{{ $index + 1 }}</td>
                    <td style="border:1px solid #000; padding:4px; vertical-align:top; white-space:pre-line;">{{ $item->jawaban?->deskripsi_hasil ?? '-' }}</td>
                    <td style="border:1px solid #000; padding:4px; vertical-align:top; white-space:pre-line;">{{ $item->jawaban?->faktor_penghambat ?? '-' }}</td>
                    <td style="border:1px solid #000; padding:4px; vertical-align:top; white-space:pre-line;">{{ $item->jawaban?->rekomendasi ?? '-' }}</td>
                    <td style="border:1px solid #000; padding:4px; vertical-align:top; white-space:pre-line;">{{ $item->jawaban?->rencana_perbaikan ?? '-' }}</td>
                    <td style="border:1px solid #000; padding:4px; vertical-align:top; white-space:pre-line;">{{ $item->jawaban?->jadwal_penyelesaian ?? '-' }}</td>
                    <td style="border:1px solid #000; padding:4px; vertical-align:top; white-space:pre-line;">{{ $item->jawaban?->pihak_tanggung_jawab ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="border:1px solid #000; padding:10px; text-align:center;">
                        Belum ada soal berkategori temuan "{{ $kategoriTemuan }}" yang diputuskan Kondisi Tidak Sesuai (KTS) pada jadwal ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @include('dokumen.partials.footer3')
</body>
</html>
