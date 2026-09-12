<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fitur baru (8 Sep 2026): Admin bisa pilih 1 dosen sebagai "Ketua Auditor" per jadwal, dari
     * panel Penugasan Auditor di JadwalAuditForm.vue. Dipakai buat 2 hal di dokumen cetak
     * (DokumenAuditController::generate()):
     * 1. Nama yang muncul di kolom tanda tangan "DISUSUN"/"DISETUJUI" (footer/footer3.blade.php,
     *    yang sebelumnya cuma ambil auditor PERTAMA dari urutan query - sekarang benar-benar
     *    yang ditunjuk Ketua).
     * 2. Urutan nama di daftar bernomor "AUDITOR" (header.blade.php) - Ketua otomatis tampil
     *    paling atas, sisanya menyusul di urutan aslinya.
     * Guard `hasColumn` biar aman dijalankan berkali-kali / di database yang mungkin sudah punya
     * kolomnya (pola sama seperti migration fix timestamps jadwal_spmi sebelumnya).
     */
    public function up(): void
    {
        Schema::table('penunjukan_auditors', function (Blueprint $table) {
            if (!Schema::hasColumn('penunjukan_auditors', 'is_ketua')) {
                $table->boolean('is_ketua')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('penunjukan_auditors', function (Blueprint $table) {
            if (Schema::hasColumn('penunjukan_auditors', 'is_ketua')) {
                $table->dropColumn('is_ketua');
            }
        });
    }
};
