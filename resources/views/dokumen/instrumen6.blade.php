<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Instrumen 6 - Permintaan Tindakan Peningkatan (PTP)</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        table { page-break-inside: auto; }
        tr { page-break-inside: avoid; page-break-after: auto; }
    </style>
</head>
<body>
    @php
        $judulDokumen = 'PERMINTAAN TINDAKAN PENINGKATAN (PTP)';
        $auditeeFieldLabel = 'PELAKSANA STANDAR';
        $labelDokumen = 'NOMOR DOKUMEN';
        $labelPeriode = 'PERIODE AUDIT MUTU INTERNAL';
        // Kop surat (10 Sep 2026) - dicocokkan persis ke contoh dokumen asli Instrumen 6 (sama
        // persis dengan Instrumen 5). Nama institusi Title Case (bukan ALL CAPS) sesuai dokumen
        // aslinya, BUKAN typo.
        $kopSuratNamaInstitusi = 'Universitas Wijayakusuma Purwokerto';
        $kopSuratBaris = [
            'Jalan Beji Karangsalam Purwokerto',
            'Telp. 02816349889',
            'Laman : www.unwiku.ac.id',
            'Email : humas@ unwiku.ac.id',
        ];
    @endphp
    @include('dokumen.partials.header')

    {{-- Instrumen 6 (jalur KS - Tindak Lanjut) cuma nampilin soal yang diputuskan KS, sama
         seperti Instrumen 3. --}}
    <table style="width:100%; border-collapse: collapse; font-size: 9.5px;">
        <thead>
            <tr>
                <th style="width:5%; border:1px solid #000; padding:4px; background:#f0f0f0;">No</th>
                <th style="width:20%; border:1px solid #000; padding:4px; background:#f0f0f0;">Deskripsi Temuan Audit</th>
                <th style="width:16%; border:1px solid #000; padding:4px; background:#f0f0f0;">Faktor Pendukung Keberhasilan</th>
                <th style="width:14%; border:1px solid #000; padding:4px; background:#f0f0f0;">Rekomendasi</th>
                <th style="width:15%; border:1px solid #000; padding:4px; background:#f0f0f0;">Rencana Peningkatan</th>
                <th style="width:15%; border:1px solid #000; padding:4px; background:#f0f0f0;">Jadwal Penyelesaian</th>
                <th style="width:15%; border:1px solid #000; padding:4px; background:#f0f0f0;">Pihak Bertanggungjawab</th>
            </tr>
        </thead>
        <tbody>
            @forelse($baris as $index => $item)
                <tr>
                    <td style="border:1px solid #000; padding:4px; text-align:center; vertical-align:top;">{{ $index + 1 }}</td>
                    <td style="border:1px solid #000; padding:4px; vertical-align:top; white-space:pre-line;">{{ $item->jawaban?->deskripsi_hasil ?? '-' }}</td>
                    <td style="border:1px solid #000; padding:4px; vertical-align:top; white-space:pre-line;">{{ $item->jawaban?->faktor_pendukung ?? '-' }}</td>
                    <td style="border:1px solid #000; padding:4px; vertical-align:top; white-space:pre-line;">{{ $item->jawaban?->rekomendasi ?? '-' }}</td>
                    <td style="border:1px solid #000; padding:4px; vertical-align:top; white-space:pre-line;">{{ $item->jawaban?->rencana_peningkatan ?? '-' }}</td>
                    <td style="border:1px solid #000; padding:4px; vertical-align:top; white-space:pre-line;">{{ $item->jawaban?->jadwal_penyelesaian ?? '-' }}</td>
                    <td style="border:1px solid #000; padding:4px; vertical-align:top; white-space:pre-line;">{{ $item->jawaban?->pihak_tanggung_jawab ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="border:1px solid #000; padding:10px; text-align:center;">
                        Belum ada soal yang diputuskan Kondisi Sesuai (KS) pada jadwal ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @include('dokumen.partials.footer3')
</body>
</html>
