<?php

// Fitur baru (15 Sep 2026) - versi PHP dari vue/src/utils/formatTeksBernomor.js, logic SAMA
// PERSIS (harus disamain kalau salah satu diubah) - dipakai di template PDF Cetak Dokumen
// (resources/views/dokumen/instrumenN.blade.php) biar teks list bernomor manual ("1) ... 2) ...")
// juga rapi jadi <ol> beneran di dokumen yang di-generate, bukan cuma di layar web.
//
// Bugfix (15 Sep 2026) - list CUMA dibentuk kalau ada MINIMAL 2 baris berurutan yang polanya
// cocok. 1 baris doang (mis. "70. Uraikan pembahasan..." - nomor urut soal ASLI dari Excel, bukan
// list manual) dikembalikan APA ADANYA, TIDAK dibungkus <ol> (kalau dipaksa <ol> 1 item, PDF
// otomatis renumber isinya jadi "1." - nomor asli hilang). Lihat catatan lengkap di
// formatTeksBernomor.js.
if (!function_exists('format_teks_bernomor')) {
    function format_teks_bernomor(?string $teks): string
    {
        if ($teks === null || $teks === '') {
            return '';
        }

        $baris = preg_split('/\r\n|\r|\n/', $teks);
        $potongan = [];
        $bufferList = []; // ['asli' => ..., 'isi' => ...][]

        $flush = function () use (&$bufferList, &$potongan) {
            if (count($bufferList) === 0) {
                return;
            }
            if (count($bufferList) === 1) {
                $potongan[] = ['tipe' => 'paragraf', 'isi' => $bufferList[0]['asli']];
            } else {
                $potongan[] = ['tipe' => 'list', 'isi' => array_map(fn ($b) => $b['isi'], $bufferList)];
            }
            $bufferList = [];
        };

        foreach ($baris as $satuBaris) {
            if (preg_match('/^\(?(\d{1,2})[.)]\s+(.*)$/', $satuBaris, $cocok)) {
                $bufferList[] = ['asli' => $satuBaris, 'isi' => $cocok[2]];
            } else {
                $flush();
                $potongan[] = ['tipe' => 'paragraf', 'isi' => $satuBaris];
            }
        }
        $flush();

        $html = '';
        foreach ($potongan as $p) {
            if ($p['tipe'] === 'list') {
                $items = implode('', array_map(
                    fn ($item) => '<li>' . e($item) . '</li>',
                    $p['isi']
                ));
                $html .= '<ol style="margin:2px 0; padding-left:20px;">' . $items . '</ol>';
            } else {
                $html .= $p['isi'] === '' ? '<br>' : '<div>' . e($p['isi']) . '</div>';
            }
        }

        return $html;
    }
}

// Fitur baru (16 Sep 2026) - versi PHP dari formatTanggalPenyelesaian() di NilaiInstrumenAuditor.vue,
// logic SAMA PERSIS (harus disamain kalau salah satu diubah) - dipakai di instrumen5.blade.php &
// instrumen6.blade.php buat nampilin "Jadwal Penyelesaian" yang sekarang diisi lewat date picker
// (tersimpan sebagai teks ISO "YYYY-MM-DD") jadi format tanggal Indonesia yang gampang dibaca di
// dokumen cetak. Data LAMA yang mungkin masih format bebas (mis. "September 2026") TIDAK dipaksa
// parse - dikembalikan apa adanya (fallback).
if (!function_exists('format_tanggal_penyelesaian')) {
    function format_tanggal_penyelesaian(?string $tgl): string
    {
        if ($tgl === null || $tgl === '') {
            return '-';
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl)) {
            return $tgl;
        }

        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        [$tahun, $bln, $hari] = explode('-', $tgl);

        return ((int) $hari) . ' ' . $bulan[(int) $bln] . ' ' . $tahun;
    }
}
