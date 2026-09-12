{{-- Partial header dokumen instrumen (dipakai instrumen1-6.blade.php).
     Variabel yang wajib dikirim dari view pemanggil:
     $judulDokumen, $standar, $jadwal, $auditeeFieldLabel, $auditeeNama, $tipeAudit,
     $periodeAudit, $auditorNama (Collection), $labelDokumen, $nomorDokumen.
     Variabel opsional: $labelPeriode (default "PERIODE AUDIT" - Instrumen 5&6 pakai label lebih
     panjang "PERIODE AUDIT MUTU INTERNAL" sesuai contoh dokumen aslinya). $kategoriTemuan (default
     null/nggak dipakai - CUMA Instrumen 5 yang isi ini, karena dokumennya digenerate PER
     KATEGORI OBS/MINOR/MAYOR, lihat DokumenAuditController.php).

     Kop surat (BARU 10 Sep 2026) - dicocokkan PERSIS ke 6 contoh dokumen asli user, yang
     ternyata teksnya beda-beda per pasangan instrumen (bukan 1 kop surat tunggal buat semua),
     jadi masing-masing instrumen1-6.blade.php WAJIB isi 2 variabel ini sebelum @include partial
     ini (kalau nggak diisi, fallback ke nama institusi generik tanpa baris alamat):
     $kopSuratNamaInstitusi (default 'UNIVERSITAS WIJAYAKUSUMA PURWOKERTO' - Instrumen 5&6 pakai
     Title Case 'Universitas Wijayakusuma Purwokerto' sesuai dokumen aslinya, BUKAN typo),
     $kopSuratBaris (array baris alamat/kontak di bawah nama institusi, urutan sesuai dokumen
     asli), $kopSuratLpmu (bool, default false - CUMA Instrumen 1&2 yang true, nampilin baris
     "LEMBAGA PENJAMINAN MUTU" bold di atas nama institusi). Logo diambil dari
     public/images/logo-unwiku.png (sama persis dgn lambang di keenam dokumen contoh). --}}
@php($labelPeriode = $labelPeriode ?? 'PERIODE AUDIT')
@php($kategoriTemuan = $kategoriTemuan ?? null)
@php($kopSuratLpmu = $kopSuratLpmu ?? false)
@php($kopSuratNamaInstitusi = $kopSuratNamaInstitusi ?? 'UNIVERSITAS WIJAYAKUSUMA PURWOKERTO')
@php($kopSuratBaris = $kopSuratBaris ?? [])
<table style="width:100%; border-collapse: collapse; margin-bottom: 10px;">
    <tr>
        <td style="width:16%; border:1px solid #000; padding:6px; text-align:center; vertical-align:middle;">
            <img src="{{ public_path('images/logo-unwiku.png') }}" style="width:55px; height:auto;">
        </td>
        <td style="width:84%; border:1px solid #000; padding:8px; text-align:center; font-size:11px; line-height:1.4;">
            @if($kopSuratLpmu)
                <div style="font-weight:bold; font-size:13px;">LEMBAGA PENJAMINAN MUTU</div>
            @endif
            <div style="font-weight:bold; font-size:13px;">{{ $kopSuratNamaInstitusi }}</div>
            @foreach($kopSuratBaris as $baris)
                <div>{{ $baris }}</div>
            @endforeach
        </td>
    </tr>
    <tr>
        <td colspan="2" style="border:1px solid #000; padding:8px; text-align:center; font-weight:bold; font-size:13px;">
            {{ $judulDokumen }}
        </td>
    </tr>
</table>

<table style="width:100%; border-collapse: collapse; font-size: 11px; margin-bottom: 10px;">
    <tr>
        <td style="width:28%; border:1px solid #000; padding:5px; font-weight:bold;">STANDAR</td>
        <td style="width:72%; border:1px solid #000; padding:5px;">{{ $standar }}</td>
    </tr>
    <tr>
        <td style="border:1px solid #000; padding:5px; font-weight:bold;">AREA AUDIT</td>
        <td style="border:1px solid #000; padding:5px;">{{ $jadwal->area_audit }}</td>
    </tr>
    <tr>
        <td style="border:1px solid #000; padding:5px; font-weight:bold;">{{ $auditeeFieldLabel }}</td>
        <td style="border:1px solid #000; padding:5px;">{{ $auditeeNama ?? '-' }}</td>
    </tr>
    <tr>
        <td style="border:1px solid #000; padding:5px; font-weight:bold;">TIPE AUDIT</td>
        <td style="border:1px solid #000; padding:5px;">{{ $tipeAudit }}</td>
    </tr>
    <tr>
        <td style="border:1px solid #000; padding:5px; font-weight:bold;">{{ $labelPeriode }}</td>
        <td style="border:1px solid #000; padding:5px;">{{ $periodeAudit }}</td>
    </tr>
    <tr>
        <td style="border:1px solid #000; padding:5px; font-weight:bold; vertical-align:top;">AUDITOR</td>
        <td style="border:1px solid #000; padding:5px;">
            Tim Auditor Area Audit:
            @if($auditorNama->isEmpty())
                <div>-</div>
            @else
                <ol style="margin:4px 0 0 16px; padding:0;">
                    @foreach($auditorNama as $nama)
                        <li>{{ $nama }}</li>
                    @endforeach
                </ol>
            @endif
        </td>
    </tr>
    @if($kategoriTemuan)
    <tr>
        <td style="border:1px solid #000; padding:5px; font-weight:bold;">KATEGORI TEMUAN</td>
        <td style="border:1px solid #000; padding:5px;">{{ $kategoriTemuan }}</td>
    </tr>
    @endif
    <tr>
        <td style="border:1px solid #000; padding:5px; font-weight:bold;">{{ $labelDokumen }}</td>
        <td style="border:1px solid #000; padding:5px;">{{ $nomorDokumen }}</td>
    </tr>
</table>
