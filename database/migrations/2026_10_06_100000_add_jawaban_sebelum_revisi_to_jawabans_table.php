<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fitur (6 Okt 2026, permintaan LPMU) - Auditor bisa melihat JAWABAN SEBELUM REVISI.
 *
 * Setiap kali Auditee mengirim revisi, jawaban lama (deskripsi_hasil sebelum ditimpa) disalin ke
 * kolom ini. Hanya menyimpan SATU versi sebelumnya (jawaban tepat sebelum revisi terakhir) -
 * bukan riwayat lengkap. Kosong = soal ini belum pernah direvisi.
 *
 * 1 kolom BARU, nullable (data lama tidak berubah, tidak ada tabel baru). Dipisah dari migration
 * direvisi_pada supaya aman walaupun migration itu sudah pernah dijalankan.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('jawabans', 'jawaban_sebelum_revisi')) {
            Schema::table('jawabans', function (Blueprint $table) {
                $table->text('jawaban_sebelum_revisi')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('jawabans', 'jawaban_sebelum_revisi')) {
            Schema::table('jawabans', function (Blueprint $table) {
                $table->dropColumn('jawaban_sebelum_revisi');
            });
        }
    }
};
