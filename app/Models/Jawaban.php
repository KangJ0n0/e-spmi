<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Jawaban extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'jawabans';

    // Awalnya persis 10 kolom (di luar id/pertanyaan_id) sesuai spek chat WA dari Pak Ezekiel.
    // "deskripsi_hasil" (Instrumen 2, field (4) di diagram) dipakai BERSAMA oleh Auditee (isi
    // jawaban/kondisi) dan Auditor (baca lalu putuskan KS/KTS) - satu kolom, dua tahap pengisian,
    // bukan dua kolom terpisah.
    //
    // Fitur baru (16 Sep 2026): 'penilaian_auditor' ditambah - rumusan/penilaian Auditor SENDIRI
    // atas deskripsi_hasil, kolom baru lewat migration add_penilaian_auditor_to_jawabans_table.
    // WAJIB ada di $fillable ini, kalau nggak Jawaban::update() di JawabanController::store()
    // DIAM-DIAM nge-drop field ini (mass assignment protection) walaupun request-nya sudah kirim
    // datanya dengan benar - baru ketauan pas verifikasi live (kolomnya kosong di DB padahal API
    // balikin sukses), bukan dari error PHP/validasi.
    protected $fillable = [
        'pertanyaan_id',
        'status_temuan',
        'deskripsi_hasil',
        'penilaian_auditor',
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
