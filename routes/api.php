<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\StrukturAnggotaController;
use App\Http\Controllers\JadwalAuditController;
use App\Http\Controllers\AuditorController;
use App\Http\Controllers\AuditeeController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\API\JawabanController;
use App\Http\Controllers\API\BankPertanyaanController;
use App\Http\Controllers\API\KategoriInstrumenController;
use App\Http\Controllers\API\BerkasInstrumenController;


Route::post('/login', [LoginController::class, 'login']);

// Endpoint publik (tanpa login) buat halaman "Struktur Organisasi" di website LPMU - sebelumnya
// halaman itu murni hardcode nama/jabatan/foto di StrukturOrganisasi.vue, sekarang narik dari
// data yang sama yang dikelola Admin di menu Struktur Anggota.
Route::get('/public/struktur-organisasi', [StrukturAnggotaController::class, 'publicStruktur']);

Route::middleware('auth:api')->group(function () {
    // Self-service, tidak dibatasi role - siapapun yang sudah login boleh logout/ganti password
    // akunnya sendiri.
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::post('/ganti-password', [LoginController::class, 'changepassword']);
    Route::post('/reset-password', [LoginController::class, 'Resetpassword']);

    // ============================================================================================
    // PERBAIKAN OTORISASI (11 Sep 2026, security review) - baca dulu sebelum ubah middleware di
    // bawah ini:
    //
    // 1) Middleware 'claim:role_name,<role>' (bisa lebih dari 1 role, pisah '|', mis.
    //    'claim:role_name,admin|auditor') membatasi endpoint berdasarkan ROLE AKUN yang login
    //    (Admin/Auditor/Auditee) - dibaca dari claim JWT `role_name` (App\Claims\CustomClaim).
    //    Sebelumnya SEMUA route di bawah cuma dibungkus 'auth:api' (cek "sudah login atau
    //    belum" doang) TANPA middleware ini - jadi role Admin/Auditor/Auditee cuma dicek di
    //    Vue Router (frontend), gampang dilewatin dengan manggil API langsung. 'claim' sendiri
    //    bukan middleware baru - itu sudah ada dari package corbosman/laravel-passport-claims
    //    (alias didaftarkan di bootstrap/app.php), cuma belum pernah dipakai sama sekali.
    //
    // 2) Middleware role di sini CUMA ngecek JENIS akun, BUKAN jadwal spesifik mana yang boleh
    //    diakses (mis. Auditor A vs Auditor B, jadwal mana yang jadi tugas masing-masing). Cek
    //    kepemilikan per-jadwal itu jenis masalah terpisah, sudah ditangani sendiri lewat
    //    App\Helper\PenugasanHelper - dipanggil langsung di dalam JawabanController::store(),
    //    JawabanController::storeAuditee(), dan DokumenAuditController::generate().
    //
    // 3) Daftar role tiap endpoint di bawah BUKAN tebakan - dicocokkan satu-satu ke halaman Vue
    //    mana saja yang benar-benar manggil endpoint itu (lihat folder views/pages/auth/admin,
    //    auditor, auditee, shared), supaya nggak ada halaman yang sah malah ikut keblokir.
    // ============================================================================================

    Route::middleware('claim:role_name,admin')->group(function () {
        Route::prefix('struktur_anggota')->group(function () {
            Route::post('/data', [StrukturAnggotaController::class, 'index']);
            Route::post('/data/store', [StrukturAnggotaController::class, 'store']);
            Route::post('/data/update', [StrukturAnggotaController::class, 'update']);
            Route::post('/data/destroy', [StrukturAnggotaController::class, 'destroy']);
        });

        // /jadwalaudit/data (index/GET) SENGAJA TIDAK di sini - dipakai juga oleh Auditor lewat
        // CetakDokumen.vue, lihat grup 'admin|auditor' di bawah. Cuma store/update/destroy yang
        // aksi tulis murni Admin.
        Route::prefix('jadwalaudit')->group(function () {
            Route::post('/data/store', [JadwalAuditController::class, 'store']);
            Route::post('/data/update', [JadwalAuditController::class, 'update']);
            Route::post('/data/destroy', [JadwalAuditController::class, 'destroy']);
            // Import Jadwal Audit dari Excel (13 Sep 2026) - tombol "Import Excel" di halaman
            // yang sama, Admin only.
            Route::post('/import', [JadwalAuditController::class, 'importExcel']);
        });

        Route::get('/dosen/get-dosen', [DosenController::class, 'getDosen']);
        // Dipanggil AdminHome.vue doang.
        Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

        // Panel "Penugasan Auditor/Auditee" (JadwalAuditForm.vue) - Admin yang nentuin siapa
        // ditugaskan ke jadwal mana. /jadwal-saya (punya Auditor/Auditee sendiri) ada di grup
        // masing-masing di bawah, BUKAN di sini.
        //
        // POST /auditor/data (index) SENGAJA TIDAK di sini lagi (14 Sep 2026, bugfix E2E) -
        // dipakai juga oleh AuditeeHome.vue buat nampilin nama Auditor yang ditugaskan di
        // dashboard Auditee sendiri, jadi dipindah ke grup 'admin|auditor|auditee' di bawah.
        // Otorisasi non-Admin (harus filter by jadwal_id + dirinya sendiri ditugaskan di jadwal
        // itu) dicek manual di dalam AuditorController::index().
        Route::prefix('auditor')->group(function () {
            Route::post('/data/store', [AuditorController::class, 'store']);
            Route::post('/data/update/{id}', [AuditorController::class, 'update']);
            Route::post('/data/destroy/{id}', [AuditorController::class, 'destroy']);
            Route::post('/data/set-ketua/{id}', [AuditorController::class, 'setKetua']);
        });

        Route::prefix('auditee')->group(function () {
            Route::post('/data', [AuditeeController::class, 'index']);
            Route::post('/data/store', [AuditeeController::class, 'store']);
            Route::post('/data/update/{id}', [AuditeeController::class, 'update']);
            Route::post('/data/destroy/{id}', [AuditeeController::class, 'destroy']);
        });

        // Tulis (kelola Bank Pertanyaan & Kategori Instrumen) - Admin only. Baca (index/show) ada
        // di grup 'admin|auditor' di bawah, karena PilihPertanyaanAuditor.vue (Auditor) juga baca
        // dari 2 endpoint ini buat nyusun daftar soal yang mau dikirim ke Auditee.
        Route::post('bank-pertanyaan', [BankPertanyaanController::class, 'store']);
        Route::put('bank-pertanyaan/{bank_pertanyaan}', [BankPertanyaanController::class, 'update']);
        Route::patch('bank-pertanyaan/{bank_pertanyaan}', [BankPertanyaanController::class, 'update']);
        // Fitur baru (15 Sep 2026) - "Hapus Semua" per kategori. WAJIB didaftarkan SEBELUM route
        // delete('bank-pertanyaan/{bank_pertanyaan}') di bawah - kalau kebalik, Laravel bakal
        // nganggep "hapus-massal" sebagai isi parameter {bank_pertanyaan} (path literal vs
        // wildcard, urutan register menentukan mana yang menang).
        Route::delete('bank-pertanyaan/hapus-massal', [BankPertanyaanController::class, 'hapusMassal']);
        Route::delete('bank-pertanyaan/{bank_pertanyaan}', [BankPertanyaanController::class, 'destroy']);
        Route::post('bank-pertanyaan/import', [BankPertanyaanController::class, 'importExcel']);

        Route::post('kategori-instrumen', [KategoriInstrumenController::class, 'store']);
        Route::put('kategori-instrumen/{kategori_instruman}', [KategoriInstrumenController::class, 'update']);
        Route::patch('kategori-instrumen/{kategori_instruman}', [KategoriInstrumenController::class, 'update']);
        Route::delete('kategori-instrumen/{kategori_instruman}', [KategoriInstrumenController::class, 'destroy']);

        // Konfigurasi Nomor Dokumen (13 Sep 2026) - Admin only, dipakai internal oleh
        // DokumenAuditController::generate() (bukan lewat HTTP, langsung query Eloquent), jadi
        // endpoint ini murni buat CRUD dari halaman Admin "Konfigurasi Dokumen".
        Route::get('konfigurasi-nomor-dokumen', [App\Http\Controllers\API\KonfigurasiNomorDokumenController::class, 'index']);
        Route::post('konfigurasi-nomor-dokumen', [App\Http\Controllers\API\KonfigurasiNomorDokumenController::class, 'store']);
        Route::delete('konfigurasi-nomor-dokumen/{id}', [App\Http\Controllers\API\KonfigurasiNomorDokumenController::class, 'destroy']);

        // Berkas Instrumen (13 Sep 2026) - repositori link dokumen pendukung instrumen (mis.
        // Google Drive). Halaman & endpoint ini Admin only (dikonfirmasi user), TIDAK dipakai
        // Auditor/Auditee sama sekali - beda dari bank-pertanyaan/kategori-instrumen di atas
        // yang baca-nya ikut dipakai role lain.
        Route::get('berkas-instrumen', [BerkasInstrumenController::class, 'index']);
        Route::post('berkas-instrumen', [BerkasInstrumenController::class, 'store']);
        Route::put('berkas-instrumen/{berkas_instrumen}', [BerkasInstrumenController::class, 'update']);
        Route::patch('berkas-instrumen/{berkas_instrumen}', [BerkasInstrumenController::class, 'update']);
        Route::delete('berkas-instrumen/{berkas_instrumen}', [BerkasInstrumenController::class, 'destroy']);
        // Route kategori-berkas-instrumen DIHAPUS (16 Sep 2026) - kategori Berkas Instrumen
        // digabung pakai kategori-instrumen yang sudah ada (lihat kategori-instrumen di grup
        // bawah & migration gabungkan_kategori_berkas_instrumen_ke_kategori_instrumen).
    });

    // Dipakai bareng Admin & Auditor: /jadwalaudit/data (index) dipanggil JadwalAudit.vue (Admin)
    // DAN CetakDokumen.vue (dipakai Admin & Auditor, lihat router/adminroute.js + auditorroute.js).
    // bank-pertanyaan & kategori-instrumen (baca) dipanggil BankPertanyaan.vue (Admin) DAN
    // PilihPertanyaanAuditor.vue (Auditor). /dokumen/{instrumen} (cetak PDF) dipanggil
    // CetakDokumen.vue - kepemilikan jadwal spesifik utk Auditor dicek lagi di dalam controller-nya
    // sendiri lewat PenugasanHelper (Admin tidak, karena Admin boleh akses semua jadwal).
    Route::middleware('claim:role_name,admin|auditor')->group(function () {
        Route::post('jadwalaudit/data', [JadwalAuditController::class, 'index']);

        Route::get('bank-pertanyaan', [BankPertanyaanController::class, 'index']);
        Route::get('bank-pertanyaan/{bank_pertanyaan}', [BankPertanyaanController::class, 'show']);
        Route::get('kategori-instrumen', [KategoriInstrumenController::class, 'index']);

        Route::get('jadwal-audit/{jadwal_id}/dokumen/{instrumen}', [App\Http\Controllers\API\DokumenAuditController::class, 'generate'])
            ->whereNumber('instrumen');
    });

    // Dipakai bareng Admin, Auditor & Auditee (14 Sep 2026, bugfix E2E): POST /auditor/data
    // (dulu admin-only) juga dipanggil AuditeeHome.vue buat nampilin nama Auditor yang
    // ditugaskan di dashboard Auditee sendiri. Otorisasi non-Admin (wajib filter jadwal_id +
    // dirinya sendiri ditugaskan di jadwal itu, TIDAK boleh akses daftar global) dicek manual
    // di dalam AuditorController::index() - middleware di sini cuma jenis akun.
    Route::middleware('claim:role_name,admin|auditor|auditee')->group(function () {
        Route::post('auditor/data', [AuditorController::class, 'index']);
    });

    // Auditor doang.
    Route::middleware('claim:role_name,auditor')->group(function () {
        Route::get('auditor/jadwal-saya', [AuditorController::class, 'jadwalSaya']);

        Route::post('list-pertanyaan', [App\Http\Controllers\API\ListPertanyaanController::class, 'store']);
        Route::delete('list-pertanyaan/{id}', [App\Http\Controllers\API\ListPertanyaanController::class, 'destroy']);

        // TAHAP 2: Auditor menilai KS/KTS. Kepemilikan jadwal spesifik dicek di dalam controller
        // lewat PenugasanHelper (lihat JawabanController::store()).
        Route::post('/jawaban/store', [JawabanController::class, 'store']);
    });

    // Auditee doang.
    Route::middleware('claim:role_name,auditee')->group(function () {
        Route::get('auditee/jadwal-saya', [AuditeeController::class, 'jadwalSaya']);

        // TAHAP 1: Auditee isi jawaban + link bukti. Kepemilikan jadwal spesifik dicek di dalam
        // controller lewat PenugasanHelper (lihat JawabanController::storeAuditee()).
        Route::post('/auditee/jawaban/store', [JawabanController::class, 'storeAuditee']);
    });

    // Dipakai bareng Auditor & Auditee: daftar soal + jawaban yang sudah ada per jadwal (dibaca
    // dari banyak halaman kedua role - PilihPertanyaanAuditor/NilaiInstrumenAuditor/AuditorHome
    // punya Auditor, IsiInstrumenAuditee/LihatHasilAuditee/AuditeeHome punya Auditee).
    Route::middleware('claim:role_name,auditor|auditee')->group(function () {
        Route::get('jadwal-audit/{jadwal_id}/pertanyaan', [App\Http\Controllers\API\ListPertanyaanController::class, 'getByJadwal']);
    });
});
