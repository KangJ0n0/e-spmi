<?php

namespace App\Helper;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Perbaikan (17 Sep 2026, laporan LPMU - dosen ditunjuk jadi auditor/auditee tapi gak bisa
 * login, "email/password salah") - akar masalahnya: data dosen di tabel `dosen` sering
 * dimasukin manual ke database (form "Tambah Dosen" di aplikasi belum pernah dibikin - lihat
 * DosenController::store(), masih kosong), dan `dosen.user_id` yang diisi manual itu kadang
 * nunjuk ke baris `userlogin` yang GAK ADA (atau memang belum pernah dibuatkan sama sekali).
 * Efeknya: dosen itu tetap bisa ditunjuk sebagai auditor/auditee (AuditorController::store()/
 * AuditeeController::store() lama cuma ngecek dosen_id ADA di tabel dosen, tidak pernah ngecek
 * akun login-nya), tapi pas dosennya coba login, LoginController gak pernah nemu akunnya -
 * gagal terus walau NIDN & password yang diketik sudah benar.
 *
 * Helper ini dipanggil dari AuditorController::store() & AuditeeController::store() SEBELUM
 * penunjukan dibuat - mastiin dosen yang mau ditunjuk itu PASTI punya akun login yang valid.
 * Kalau belum ada, otomatis dibikinin (NIDN dipakai sebagai username DAN password awal - sama
 * kayak konvensi Reset Password yang sudah ada di LoginController::Resetpassword(), yang reset
 * password ke `name`/username-nya sendiri).
 */
class AkunDosenHelper
{
    /**
     * @param  string  $dosenId
     * @return \Illuminate\Http\JsonResponse|null  null kalau akun sudah/berhasil dipastikan
     *                                              ada, JsonResponse (400) kalau gagal
     *                                              (mis. NIDN kosong, jadi gak ada username
     *                                              yang bisa dipakai).
     */
    public static function pastikanAkunLogin(string $dosenId)
    {
        $dosen = DB::table('dosen')->where('id', $dosenId)->first();

        if (!$dosen) {
            return response()->json(['error' => 'Data dosen tidak ditemukan.'], 400);
        }

        // Sudah punya akun login yang valid (user_id-nya beneran nunjuk ke baris userlogin
        // yang ada) - tidak perlu apa-apa lagi, jangan diutak-atik.
        if ($dosen->user_id) {
            $adaAkun = DB::table('userlogin')->where('id', $dosen->user_id)->exists();
            if ($adaAkun) {
                return null;
            }
        }

        $username = trim((string) ($dosen->nidn ?? ''));
        if ($username === '') {
            return response()->json([
                'error' => "Dosen \"{$dosen->nama_dosen}\" belum punya akun login DAN belum punya NIDN di data dosen - isi dulu NIDN-nya (dipakai sebagai username login) sebelum bisa ditugaskan.",
            ], 400);
        }

        // Mungkin akunnya sebenarnya SUDAH ADA (dibuat sebelumnya) tapi user_id di dosen cuma
        // belum/salah ke-link - sambungin ke situ, JANGAN bikin baru (login_name harus unique).
        $userlogin = DB::table('userlogin')
            ->where('name', $username)
            ->orWhere('login_name', $username)
            ->first();

        if (!$userlogin) {
            $newId = (string) Str::uuid();
            DB::table('userlogin')->insert([
                'id' => $newId,
                'login_name' => $username,
                'name' => $username,
                'password' => Hash::make($username),
                'is_active' => '1',
                'is_deleted' => '0',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $userloginId = $newId;
        } else {
            $userloginId = $userlogin->id;
        }

        DB::table('dosen')->where('id', $dosenId)->update(['user_id' => $userloginId]);

        $adaRole = DB::table('role_user')->where('user_id', $userloginId)->exists();
        if (!$adaRole) {
            // Fix (17 Sep 2026) - kolom `id` di tabel `role_user` di server ternyata BUKAN
            // auto-increment (beda dari migration aslinya), jadi insert tanpa `id` gagal
            // ("Field 'id' doesn't have a default value"). Ngasih `id` manual (MAX+1) sebagai
            // jaga-jaga, aman dipakai walau di database lain kolomnya beneran auto-increment.
            $idBaru = (int) (DB::table('role_user')->max('id')) + 1;
            DB::table('role_user')->insert(['id' => $idBaru, 'user_id' => $userloginId, 'role_id' => 2]);
        }

        return null;
    }
}
