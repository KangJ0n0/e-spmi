<?php

namespace App\Helper;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Cek apakah dosen yang sedang login BENERAN ditugaskan (lewat tabel `penunjukan_auditors`)
 * pada jadwal audit tertentu, sebelum dia boleh baca/tulis data jadwal itu.
 *
 * LATAR BELAKANG (security review 11 Sep 2026): sebelum helper ini ada, endpoint kayak
 * JawabanController::store()/storeAuditee() dan DokumenAuditController::generate() cuma
 * ngecek jadwalnya ADA dan masih dalam rentang tanggal (lihat JawabanController::
 * cekJadwalDanTanggal()) - TIDAK PERNAH ngecek apakah dosen yang login itu beneran ditunjuk
 * di jadwal SPESIFIK yang diakses. Efeknya: Auditor A yang cuma ditugaskan di Jadwal X tetap
 * bisa baca/isi/nilai jawaban Jadwal Y meski dia sama sekali nggak ada di penunjukan_auditors
 * buat Jadwal Y - dibuktikan langsung pakai jadwal test tanpa penugasan sama sekali.
 *
 * Akun Admin TIDAK dicek di sini (otomatis lolos) - Admin memang berhak akses semua jadwal, dan
 * Admin tidak punya baris di tabel `dosen` sama sekali (pola deteksi yang sama persis dipakai
 * CustomClaim.php buat nentuin role_name pas login: ada baris dosen -> auditor/auditee, nggak
 * ada -> admin).
 */
class PenugasanHelper
{
    /**
     * @param Request $request  request yang sedang login (dipakai buat ambil user login)
     * @param string  $jadwalId jadwal_spmi_id yang mau diakses/diproses
     * @param string  $status   'auditor' atau 'auditee' - role yang wajib cocok di penunjukan_auditors
     * @return \Illuminate\Http\JsonResponse|null null kalau lolos, JsonResponse (403) kalau ditolak
     */
    public static function cekPenugasan(Request $request, $jadwalId, $status)
    {
        $user = $request->user();

        $dosen = DB::table('dosen')->where('user_id', $user->id)->first();

        if (!$dosen) {
            // Tidak ada baris dosen = akun Admin (lihat docblock class ini) - Admin boleh akses
            // jadwal manapun, jadi lolos tanpa dicek lebih lanjut.
            return null;
        }

        $ditugaskan = DB::table('penunjukan_auditors')
            ->where('jadwal_spmi_id', $jadwalId)
            ->where('dosen_id', $dosen->id)
            ->where('status', $status)
            ->exists();

        if (!$ditugaskan) {
            return response()->json([
                'error' => 'Anda tidak ditugaskan sebagai ' . $status . ' pada jadwal ini.',
            ], 403);
        }

        return null;
    }
}
