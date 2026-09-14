<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fitur baru (13 Sep 2026): client minta Admin bisa atur sendiri Nomor Dokumen Instrumen
     * 1-6 (sebelumnya hardcode di DokumenAuditController::NOMOR_DOKUMEN, sama buat semua jadwal
     * & semua periode selamanya). Konfirmasi user: nomor dokumen kemungkinan SAMA selama 1
     * PERIODE (semester) - padahal 1 periode bisa punya BANYAK jadwal (mis. beberapa fakultas
     * diaudit di jadwal terpisah tapi semester yang sama). Solusinya: konfigurasi disimpan PER
     * SEMESTER (bukan per jadwal) - `semester` dibuat UNIQUE, jadi 1 baris konfigurasi otomatis
     * berlaku ke SEMUA jadwal yang semester-nya sama, Admin cuma isi 1x per semester.
     *
     * Semua kolom nomor_dokumen_1..6 NULLABLE dengan sengaja - kalau Admin cuma isi sebagian
     * (mis. baru sempat isi Instrumen 1-3), Instrumen yang belum diisi fallback ke default
     * hardcode lama (lihat DokumenAuditController::resolveNomorDokumen()), jadi nggak ada
     * dokumen yang tiba-tiba nomornya kosong/error kalau Admin belum sempat lengkapi semuanya.
     */
    public function up(): void
    {
        if (!Schema::hasTable('konfigurasi_nomor_dokumen')) {
            Schema::create('konfigurasi_nomor_dokumen', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('semester', 10)->unique();
                $table->string('nomor_dokumen_1')->nullable();
                $table->string('nomor_dokumen_2')->nullable();
                $table->string('nomor_dokumen_3')->nullable();
                $table->string('nomor_dokumen_4')->nullable();
                $table->string('nomor_dokumen_5')->nullable();
                $table->string('nomor_dokumen_6')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('konfigurasi_nomor_dokumen');
    }
};
