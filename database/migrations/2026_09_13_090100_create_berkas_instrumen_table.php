<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel isi "Berkas Instrumen" (13 Sep 2026) - tiap baris = 1 link dokumen pendukung
     * instrumen (nama, keterangan, link - biasanya link Google Drive, tapi tidak dibatasi cuma
     * itu, lihat catatan embed-preview di BerkasInstrumen.vue). Kolom `kategori_berkas_instrumen_id`
     * NULLABLE + `nullOnDelete()` - sama seperti pola `bank_pertanyaans.kategori_instrumen_id`,
     * kalau kategorinya dihapus, berkas yang tadinya masuk situ TETAP ADA, cuma balik jadi
     * "Tanpa Kategori" (bukan ikut terhapus).
     */
    public function up(): void
    {
        if (!Schema::hasTable('berkas_instrumen')) {
            Schema::create('berkas_instrumen', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('kategori_berkas_instrumen_id')->nullable();
                $table->string('nama');
                $table->text('keterangan')->nullable();
                $table->string('link', 2048);
                $table->timestamps();

                $table->foreign('kategori_berkas_instrumen_id')
                      ->references('id')
                      ->on('kategori_berkas_instrumen')
                      ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('berkas_instrumen');
    }
};
