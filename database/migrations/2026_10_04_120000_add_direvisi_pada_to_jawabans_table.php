<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fitur (4 Okt 2026) - info "Direvisi pada" di jawaban Auditee.
 *
 * Setiap kali Auditee mengirim REVISI jawaban (soal yang sudah pernah dijawab), sistem mencatat
 * waktunya di kolom ini, lalu ditampilkan ke Auditor & Auditee sebagai "Direvisi pada <tanggal>".
 * Revisi TIDAK digerakkan permintaan Auditor (sengaja ditunda dulu atas permintaan user) -
 * semua soal yang sudah dijawab tetap boleh direvisi seperti aturan 1 Okt.
 *
 * 1 kolom BARU, nullable (data lama tidak berubah, tidak ada tabel baru). Kosong = belum pernah
 * direvisi. Kolom ini pernah ada di sebagian server (fitur lama yang dicabut 1 Okt), jadi dicek
 * dulu biar migration tidak error "duplicate column".
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('jawabans', 'direvisi_pada')) {
            Schema::table('jawabans', function (Blueprint $table) {
                $table->timestamp('direvisi_pada')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('jawabans', 'direvisi_pada')) {
            Schema::table('jawabans', function (Blueprint $table) {
                $table->dropColumn('direvisi_pada');
            });
        }
    }
};
