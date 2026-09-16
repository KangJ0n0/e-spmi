<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Gabungkan Kategori Berkas Instrumen ke Kategori Instrumen (16 Sep 2026)
 *
 * Sebelumnya (13 Sep) "Kategori Berkas Instrumen" SENGAJA dibuat terpisah total dari "Kategori
 * Instrumen" (dikonfirmasi user waktu itu). User sekarang berubah pikiran: "kategori pada
 * instrumen dan berkas instrumen sama, jadi dihubungkan saja" - jadi halaman Berkas Instrumen
 * sekarang PAKAI kategori yang SAMA dengan halaman Instrumen (`kategori_instrumen`), bukan
 * tabel/daftar sendiri lagi.
 *
 * SENGAJA pakai migration BARU (forward), bukan edit/hapus migration lama yang sudah pernah
 * `php artisan migrate` - konsisten dengan cara semua migration lain di project ini menangani
 * perubahan skema (mis. cleanup_orphan_penunjukan_auditors), bukan mengedit riwayat migration
 * yang sudah jalan.
 *
 * Data lama (kalau ada baris `berkas_instrumen` yang sudah punya kategori) TIDAK hilang - nama
 * kategori lamanya dicari/dibikinkan padanannya di `kategori_instrumen` (find-or-create by nama),
 * baru baris `berkas_instrumen`-nya disambungkan ke situ.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('berkas_instrumen', function (Blueprint $table) {
            $table->uuid('kategori_instrumen_id')->nullable()->after('kategori_berkas_instrumen_id');
        });

        // Pindahkan data lama (kalau ada) - cari/bikin kategori dengan NAMA yang sama di
        // kategori_instrumen, baru sambungkan berkas_instrumen ke situ.
        if (Schema::hasTable('kategori_berkas_instrumen')) {
            $kategoriLama = DB::table('kategori_berkas_instrumen')->get(['id', 'nama']);
            foreach ($kategoriLama as $lama) {
                $kategoriBaru = DB::table('kategori_instrumen')->where('nama', $lama->nama)->first();
                if (!$kategoriBaru) {
                    $idBaru = (string) Str::uuid();
                    DB::table('kategori_instrumen')->insert([
                        'id' => $idBaru,
                        'nama' => $lama->nama,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $idBaru = $kategoriBaru->id;
                }

                DB::table('berkas_instrumen')
                    ->where('kategori_berkas_instrumen_id', $lama->id)
                    ->update(['kategori_instrumen_id' => $idBaru]);
            }
        }

        Schema::table('berkas_instrumen', function (Blueprint $table) {
            $table->foreign('kategori_instrumen_id')
                  ->references('id')
                  ->on('kategori_instrumen')
                  ->nullOnDelete();
            $table->dropForeign(['kategori_berkas_instrumen_id']);
            $table->dropColumn('kategori_berkas_instrumen_id');
        });

        Schema::dropIfExists('kategori_berkas_instrumen');
    }

    public function down(): void
    {
        Schema::create('kategori_berkas_instrumen', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama')->unique();
            $table->timestamps();
        });

        Schema::table('berkas_instrumen', function (Blueprint $table) {
            $table->uuid('kategori_berkas_instrumen_id')->nullable()->after('id');
        });

        Schema::table('berkas_instrumen', function (Blueprint $table) {
            $table->foreign('kategori_berkas_instrumen_id')
                  ->references('id')
                  ->on('kategori_berkas_instrumen')
                  ->nullOnDelete();
            $table->dropForeign(['kategori_instrumen_id']);
            $table->dropColumn('kategori_instrumen_id');
        });
    }
};
