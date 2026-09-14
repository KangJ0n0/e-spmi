<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fitur baru "Berkas Instrumen" (13 Sep 2026) - halaman Admin buat nyimpen link dokumen
     * pendukung instrumen (mis. link Google Drive ke RPS, Absensi, dll), TERPISAH dari
     * "Kategori Instrumen" (`kategori_instrumen`) yang sudah ada buat Bank Pertanyaan -
     * dikonfirmasi user: kategori baru sendiri, bukan numpang yang lama, biar 2 "ruang" ini
     * (soal instrumen vs berkas/dokumen instrumen) tidak saling campur. Struktur tabel sengaja
     * dibuat identik dengan `kategori_instrumen` (lihat migration
     * `create_kategori_instrumen_table`) - murni daftar nama kategori, dikelola dari halaman
     * Admin "Berkas Instrumen" (KategoriBerkasInstrumenModal.vue).
     */
    public function up(): void
    {
        if (!Schema::hasTable('kategori_berkas_instrumen')) {
            Schema::create('kategori_berkas_instrumen', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('nama')->unique();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_berkas_instrumen');
    }
};
