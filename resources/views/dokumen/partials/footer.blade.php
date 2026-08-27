{{-- Partial footer dokumen instrumen: blok VALIDASI DAN CATATAN (DISUSUN/DISETUJUI) + CATATAN.
     Variabel yang wajib: $auditorNama (Collection), $auditeeNama --}}
<table style="width:100%; border-collapse: collapse; font-size: 11px; margin-top: 12px;">
    <tr>
        <td colspan="2" style="border:1px solid #000; padding:5px; font-weight:bold; text-align:center;">
            VALIDASI DAN CATATAN
        </td>
    </tr>
    <tr>
        <td style="width:50%; border:1px solid #000; padding:8px; vertical-align:top;">
            <div style="font-weight:bold; text-align:center; margin-bottom:26px;">DISUSUN</div>
            <div>Oleh&nbsp;&nbsp;&nbsp;: {{ $auditorNama->first() ?? '-' }}</div>
            <div style="margin-top:22px;">Tanggal : ..........................</div>
            <div style="margin-top:12px;">Paraf&nbsp;&nbsp;&nbsp;: ..........................</div>
        </td>
        <td style="width:50%; border:1px solid #000; padding:8px; vertical-align:top;">
            <div style="font-weight:bold; text-align:center; margin-bottom:26px;">DISETUJUI</div>
            <div>Oleh&nbsp;&nbsp;&nbsp;: {{ $auditeeNama ?? '-' }}</div>
            <div style="margin-top:22px;">Tanggal : ..........................</div>
            <div style="margin-top:12px;">Paraf&nbsp;&nbsp;&nbsp;: ..........................</div>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="border:1px solid #000; padding:5px; font-weight:bold;">CATATAN</td>
    </tr>
    <tr>
        <td colspan="2" style="border:1px solid #000; padding:5px; height:55px;">&nbsp;</td>
    </tr>
</table>
