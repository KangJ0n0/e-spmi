<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Jawaban extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'jawabans';

    // Persis 10 kolom (di luar id/pertanyaan_id) sesuai spek chat WA dari Pak Ezekiel — TIDAK ADA
    // kolom tambahan. "deskripsi_hasil" (Instrumen 2, field (4) di diagram) dipakai BERSAMA oleh
    // Auditee (isi jawaban/kondisi) dan Auditor (baca lalu putuskan KS/KTS) - satu kolom, dua
    // tahap pengisian, bukan dua kolom terpisah.
    protected $fillable = [
        'pertanyaan_id',
        'status_temuan',
        'deskripsi_hasil',
        'faktor_pendukung',
        'rencana_peningkatan',
        'kategori_temuan',
        'faktor_penghambat',
        'rekomendasi',
        'rencana_perbaikan',
        'jadwal_penyelesaian',
        'pihak_tanggung_jawab',
    ];

    /**
     * PENTING: kolom `pertanyaan_id` di tabel ini nunjuk ke `list_pertanyaans.id`, BUKAN
     * `bank_pertanyaans.id` langsung (walau namanya masih "pertanyaan_id" - nggak di-rename
     * biar zero migration). Ini supaya 1 baris jawaban selalu terikat ke satu soal-di-jadwal
     * tertentu, bukan ke soal generiknya di bank (yang bisa dipakai ulang di banyak jadwal
     * lain). Lihat JawabanController::cekJadwalDanTanggal() buat penjelasan lengkap.
     */
    public function listPertanyaan()
    {
        return $this->belongsTo(ListPertanyaan::class, 'pertanyaan_id');
    }
}
