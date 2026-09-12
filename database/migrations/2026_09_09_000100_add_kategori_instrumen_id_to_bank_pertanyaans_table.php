<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lihat komentar lengkap di migration `create_kategori_instrumen_table` (9 Sep 2026).
     * Kolom NULLABLE + `nullOnDelete()` (bukan `cascade`) - kalau kategorinya dihapus, soal
     * yang tadinya masuk kategori itu TETAP ADA, cuma balik jadi "Tanpa Kategori" (bukan ikut
     * terhapus). Guard `hasColumn()` (pola sama kayak migration fix `jadwal_spmi`/
     * `penunjukan_auditors` sebelumnya) biar aman dijalankan berkali-kali.
     */
    public function up(): void
    {
        Schema::table('bank_pertanyaans', function (Blueprint $table) {
            if (!Schema::hasColumn('bank_pertanyaans', 'kategori_instrumen_id')) {
                $table->uuid('kategori_instrumen_id')->nullable()->after('id');
                $table->foreign('kategori_instrumen_id')
                      ->references('id')
                      ->on('kategori_instrumen')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('bank_pertanyaans', function (Blueprint $table) {
            if (Schema::hasColumn('bank_pertanyaans', 'kategori_instrumen_id')) {
                $table->dropForeign(['kategori_instrumen_id']);
                $table->dropColumn('kategori_instrumen_id');
            }
        });
    }
};
