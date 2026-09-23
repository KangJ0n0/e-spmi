<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Instrumen 1 - Check List</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        table { page-break-inside: auto; }
        tr { page-break-inside: avoid; page-break-after: auto; }
    </style>
</head>
<body>
    @php
        $judulDokumen = 'CHECK LIST';
        $auditeeFieldLabel = 'AUDITEE';
        $labelDokumen = 'KODE DOKUMEN';
        // Kop surat (10 Sep 2026) - dicocokkan persis ke contoh dokumen asli Instrumen 1.
        $kopSuratLpmu = true;
        $kopSuratBaris = [
            'Jl. Raya Beji Karangsalam Purwokerto',
            'www.unwiku.ac.id',
            'Telp.(0281) 6439889, Fax. 6439711',
        ];
    @endphp
    @include('dokumen.partials.header')

    <table style="width:100%; border-collapse: collapse; font-size: 10.5px;">
        <thead>
            <tr>
                <th style="width:6%; border:1px solid #000; padding:5px; background:#f0f0f0;">NO</th>
                <th style="width:28%; border:1px solid #000; padding:5px; background:#f0f0f0;">Pernyataan Isi Standar</th>
                <th style="width:38%; border:1px solid #000; padding:5px; background:#f0f0f0;">Butir Pertanyaan</th>
                <th style="width:28%; border:1px solid #000; padding:5px; background:#f0f0f0;">Dokumen Akan Dicheck</th>
            </tr>
        </thead>
        <tbody>
            @forelse($baris as $index => $item)
                <tr>
                    <td style="border:1px solid #000; padding:5px; text-align:center; vertical-align:top;">{{ $index + 1 }}</td>
                    <td style="border:1px solid #000; padding:5px; vertical-align:top; ">{!! format_teks_bernomor($item->pertanyaan?->pertanyaan) !!}</td>
                    <td style="border:1px solid #000; padding:5px; vertical-align:top; ">{!! format_teks_bernomor($item->pertanyaan?->butir_pertanyaan) !!}</td>
                    <td style="border:1px solid #000; padding:5px; vertical-align:top; ">{!! format_teks_bernomor($item->pertanyaan?->dokumen_cek) !!}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="border:1px solid #000; padding:10px; text-align:center;">
                        Belum ada soal pada jadwal ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @include('dokumen.partials.footer')
</body>
</html>
