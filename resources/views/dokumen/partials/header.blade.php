{{-- Partial header dokumen instrumen (dipakai instrumen1-6.blade.php).
     Variabel yang wajib dikirim dari view pemanggil:
     $judulDokumen, $standar, $jadwal, $auditeeFieldLabel, $auditeeNama, $tipeAudit,
     $periodeAudit, $auditorNama (Collection), $labelDokumen, $nomorDokumen.
     Variabel opsional: $labelPeriode (default "PERIODE AUDIT" - Instrumen 5&6 pakai label lebih
     panjang "PERIODE AUDIT MUTU INTERNAL" sesuai contoh dokumen aslinya). $kategoriTemuan (default
     null/nggak dipakai - CUMA Instrumen 5 yang isi ini, karena dokumennya digenerate PER
     KATEGORI OBS/MINOR/MAYOR, lihat DokumenAuditController.php). --}}
@php($labelPeriode = $labelPeriode ?? 'PERIODE AUDIT')
@php($kategoriTemuan = $kategoriTemuan ?? null)
<table style="width:100%; border-collapse: collapse; margin-bottom: 10px; font-size: 11px;">
    <tr>
        <td style="width:60%; border:1px solid #000; padding:8px; font-weight:bold; text-align:center; font-size:13px;">
            UNIVERSITAS WIJAYAKUSUMA PURWOKERTO
        </td>
        <td style="width:40%; border:1px solid #000; padding:8px; text-align:center; font-weight:bold; font-size:13px;">
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
