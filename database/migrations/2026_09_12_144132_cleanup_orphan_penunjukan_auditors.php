<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migration data-cleanup (bukan perubahan skema) - `penunjukan_auditors.jadwal_spmi_id`
     * TIDAK PERNAH punya foreign key constraint sejak awal (beda dengan `list_pertanyaans` yang
     * sudah benar pakai onDelete('cascade') ke jadwal_spmi & bank_pertanyaans). Akibatnya, tiap
     * kali ada Jadwal Audit yang dihapus SEBELUM fix ini (lihat JadwalAuditController::destroy()),
     * baris penugasan Auditor/Auditee terkait ketinggalan jadi baris "orphan" - jadwal_spmi_id-nya
     * nunjuk ke jadwal yang sudah nggak ada. Baris orphan ini ikut kebaca leftJoin di
     * AuditorController/AuditeeController::index(), jadi nama dosen yang jadwalnya sudah dihapus
     * tetap muncul di halaman Admin > Auditor/Auditee.
     *
     * Migration ini nyapu baris orphan yang SUDAH TERLANJUR ada di database sekarang. Aman
     * dijalankan berkali-kali (idempotent) - kalau nggak ada orphan, nggak ada yang kehapus.
     * Nggak ada perlu di-`down()` balikin - data yang dihapus di sini memang data sampah yang
     * jadwalnya sudah nggak ada, nggak ada cara "mengembalikan" secara bermakna.
     */
    public function up(): void
    {
        DB::table('penunjukan_auditors')
            ->whereNotIn('jadwal_spmi_id', function ($query) {
                $query->select('id')->from('jadwal_spmi');
            })
            ->delete();
    }

    public function down(): void
    {
        // Sengaja kosong - lihat catatan di atas, data orphan yang dihapus bukan data yang
        // bermakna buat dikembalikan.
    }
};
