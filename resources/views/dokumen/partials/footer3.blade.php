{{-- Partial footer 3-KOLOM (DISUSUN/DISETUJUI/DIVALIDASI), dipakai Instrumen 5 (PTK) & 6 (PTP) -
     beda dari partials/footer.blade.php (2 kolom) yang dipakai Instrumen 1-4.
     PENTING - peran DISUSUN/DISETUJUI di sini KEBALIK dari Instrumen 1-4 (dicocokkan ke contoh
     dokumen asli Instrumen 5 & 6 user): DISUSUN = Auditee (pihak yang menyusun rencana tindak
     lanjut/perbaikan), DISETUJUI = Auditor (yang menyetujui rencananya). DIVALIDASI = field
     manual baru (nama pihak ketiga, mis. Ketua LPMU) - lihat catatan di
     DokumenAuditController::generate().
     Variabel yang wajib: $auditorNama (Collection), $auditeeNama, $divalidasi --}}
<table style="width:100%; border-collapse: collapse; font-size: 10px; margin-top: 12px;">
    <tr>
        <td colspan="3" style="border:1px solid #000; padding:5px; font-weight:bold; text-align:center;">
            VALIDASI DAN CATATAN
        </td>
    </tr>
    <tr>
        <td style="width:34%; border:1px solid #000; padding:6px; vertical-align:top;">
            <div style="font-weight:bold; text-align:center; margin-bottom:20px;">DISUSUN</div>
            <div>Oleh&nbsp;&nbsp;&nbsp;: {{ $auditeeNama ?? '-' }}</div>
            <div style="margin-top:18px;">Tanggal : ..........................</div>
            <div style="margin-top:10px;">Tanda Tangan : ..........................</div>
        </td>
        <td style="width:33%; border:1px solid #000; padding:6px; vertical-align:top;">
            <div style="font-weight:bold; text-align:center; margin-bottom:20px;">DISETUJUI</div>
            <div>Oleh&nbsp;&nbsp;&nbsp;: {{ $auditorNama->first() ?? '-' }}</div>
            <div style="margin-top:18px;">Tanggal : ..........................</div>
            <div style="margin-top:10px;">Tanda Tangan : ..........................</div>
        </td>
        <td style="width:33%; border:1px solid #000; padding:6px; vertical-align:top;">
            <div style="font-weight:bold; text-align:center; margin-bottom:20px;">DIVALIDASI</div>
            <div>Oleh&nbsp;&nbsp;&nbsp;: {{ $divalidasi ?? '-' }}</div>
            <div style="margin-top:18px;">Tanggal : ..........................</div>
            <div style="margin-top:10px;">Tanda Tangan : ..........................</div>
        </td>
    </tr>
    <tr>
        <td colspan="3" style="border:1px solid #000; padding:5px; font-weight:bold;">CATATAN</td>
    </tr>
    <tr>
        <td colspan="3" style="border:1px solid #000; padding:5px; height:55px;">&nbsp;</td>
    </tr>
</table>
