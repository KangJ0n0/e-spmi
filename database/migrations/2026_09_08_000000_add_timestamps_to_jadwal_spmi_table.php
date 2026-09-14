<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration ini nge-fix bug nyata: migration asal `jadwal_spmi` (2026_08_06_033718) nggak
     * pernah punya $table->timestamps(), padahal model JadwalAudit (Eloquent default) selalu
     * nyoba isi created_at/updated_at tiap insert/update. Efeknya: "Tambah Jadwal" gagal dengan
     * error SQL "table jadwal_spmi has no column named updated_at" di database mana pun yang
     * dimigrasikan dari file lama itu (termasuk database yang sudah jalan sekarang).
     *
     * Ini migration TAMBAHAN (bukan edit file lama) supaya bisa langsung dijalankan
     * (`php artisan migrate`) di atas database yang SUDAH ADA tanpa perlu migrate:fresh /
     * kehilangan data - cukup nambah 2 kolom yang belum ada.
     */
    public function up(): void
    {
        Schema::table('jadwal_spmi', function (Blueprint $table) {
            if (!Schema::hasColumn('jadwal_spmi', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('jadwal_spmi', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_spmi', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at']);
        });
    }
};
