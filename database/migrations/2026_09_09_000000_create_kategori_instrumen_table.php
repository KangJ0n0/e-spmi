<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fitur baru (9 Sep 2026): client minta Admin bisa bikin "ruang" instrumen sendiri
     * (contoh: LAMEMBA) di luar soal Instrumen bawaan, lalu upload/tambah soal khusus di
     * situ, dan Auditor bisa pilih per kategori pas ngirim soal ke Auditee (lihat
     * PilihPertanyaanAuditor.vue). Tabel ini murni daftar nama kategori (mis. "LAMEMBA"),
     * dikelola dari halaman Instrumen (KategoriInstrumenModal.vue). Soal (`bank_pertanyaans`)
     * dikaitkan ke sini lewat kolom `kategori_instrumen_id` (NULLABLE - lihat migration
     * susulan `add_kategori_instrumen_id_to_bank_pertanyaans_table`), soal lama yang belum
     * dikategorikan TETAP muncul sebagai "Tanpa Kategori", tidak wajib diisi ulang.
     */
    public function up(): void
    {
        if (!Schema::hasTable('kategori_instrumen')) {
            Schema::create('kategori_instrumen', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('nama')->unique();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_instrumen');
    }
};
