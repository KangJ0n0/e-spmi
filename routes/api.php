<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\StrukturAnggotaController;
use App\Http\Controllers\JadwalAuditController;
use App\Http\Controllers\AuditorController;
use App\Http\Controllers\AuditeeController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\API\JawabanController;
use App\Http\Controllers\API\BankPertanyaanController;


Route::post('/login', [LoginController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::post('/ganti-password', [LoginController::class, 'changepassword']);
    Route::post('/reset-password', [LoginController::class, 'Resetpassword']);
    Route::prefix('struktur_anggota')->group(function () {
        Route::post('/data', [StrukturAnggotaController::class, 'index']);
        Route::post('/data/store', [StrukturAnggotaController::class, 'store']);
        Route::post('/data/update', [StrukturAnggotaController::class, 'update']);
        Route::post('/data/destroy', [StrukturAnggotaController::class, 'destroy']);

    });
    Route::prefix('jadwalaudit')->group(function () {
        Route::post('/data', [JadwalAuditController::class, 'index']);
        Route::post('/data/store', [JadwalAuditController::class, 'store']);
        Route::post('/data/update', [JadwalAuditController::class, 'update']);
        Route::post('/data/destroy', [JadwalAuditController::class, 'destroy']);
    });
    Route::get('/dosen/get-dosen', [DosenController::class, 'getDosen']);

    Route::prefix('auditor')->group(function () {
        Route::post('/data', [AuditorController::class, 'index']);
        Route::post('/data/store', [AuditorController::class, 'store']);
        Route::post('/data/update/{id}', [AuditorController::class, 'update']);
        Route::post('/data/destroy/{id}', [AuditorController::class, 'destroy']);
        // Dipanggil JadwalAuditor.vue - sebelumnya route ini belum pernah dibuat sama sekali.
        Route::get('/jadwal-saya', [AuditorController::class, 'jadwalSaya']);
    });

    Route::prefix('auditee')->group(function () {
        Route::post('/data', [AuditeeController::class, 'index']);
        Route::post('/data/store', [AuditeeController::class, 'store']);
        Route::post('/data/update/{id}', [AuditeeController::class, 'update']);
        Route::post('/data/destroy/{id}', [AuditeeController::class, 'destroy']);
        // Dipanggil JadwalAuditee.vue - sama seperti /auditor/jadwal-saya, belum pernah dibuat sama sekali.
        Route::get('/jadwal-saya', [AuditeeController::class, 'jadwalSaya']);
    });

    Route::apiResource('bank-pertanyaan', BankPertanyaanController::class);
    Route::post('bank-pertanyaan/import', [BankPertanyaanController::class, 'importExcel']);

    Route::get('jadwal-audit/{jadwal_id}/pertanyaan', [App\Http\Controllers\API\ListPertanyaanController::class, 'getByJadwal']);
    Route::post('list-pertanyaan', [App\Http\Controllers\API\ListPertanyaanController::class, 'store']);
    Route::delete('list-pertanyaan/{id}', [App\Http\Controllers\API\ListPertanyaanController::class, 'destroy']);
    // TAHAP 1: Auditee isi jawaban + link bukti.
    Route::post('/auditee/jawaban/store', [JawabanController::class, 'storeAuditee']);
    // TAHAP 2: Auditor menilai KS/KTS (baru bisa setelah TAHAP 1 selesai).
    Route::post('/jawaban/store', [JawabanController::class, 'store']);

    // Cetak dokumen resmi Instrumen 1-4 (PDF) - dipakai halaman CetakDokumen.vue (Admin & Auditor).
    // Instrumen 5 & 6 SENGAJA belum ada route-nya - user belum kasih contoh dokumennya.
    // `standar` & `tipe_audit` dikirim sebagai query param (diisi manual di form, tidak ada di DB).
    Route::get('jadwal-audit/{jadwal_id}/dokumen/{instrumen}', [App\Http\Controllers\API\DokumenAuditController::class, 'generate'])
        ->whereNumber('instrumen');
});