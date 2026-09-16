<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fitur baru (16 Sep 2026) - kolom "Penilaian Auditor", tempat Auditor menuliskan penilaian
 * sendiri atas jawaban Auditee, DIISI SEBELUM Auditor menentukan KS/KTS (lihat
 * NilaiInstrumenAuditor.vue). Dikonfirmasi user lewat AskUserQuestion: field ini WAJIB kolom
 * database baru (ke-11 kolom `jawabans` yang sudah ada semuanya sudah kepakai, tidak ada yang
 * bisa dipakai ulang).
 *
 * Nilai kolom ini JUGA menggantikan `deskripsi_hasil` (jawaban Auditee) di SEMUA cetak dokumen
 * yang sebelumnya menampilkan jawaban Auditee (Instrumen 2, 3, 4, 5, 6 - dikonfirmasi user lewat
 * AskUserQuestion, cakupannya "semua yang masih pakai Jawaban Auditee") - lihat
 * DokumenAuditController.php & resources/views/dokumen/instrumen{2,3,4,5,6}.blade.php.
 * `deskripsi_hasil` sendiri TETAP ADA & TETAP DIISI Auditee seperti biasa (tidak dihapus) - cuma
 * tidak lagi dipakai buat isi kolom "Deskripsi Hasil/Temuan Audit" di dokumen cetak, tetap dipakai
 * sebagai konteks/referensi yang dibaca Auditor di halaman Nilai Instrumen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jawabans', function (Blueprint $table) {
            $table->text('penilaian_auditor')->nullable()->after('deskripsi_hasil');
        });
    }

    public function down(): void
    {
        Schema::table('jawabans', function (Blueprint $table) {
            $table->dropColumn('penilaian_auditor');
        });
    }
};
