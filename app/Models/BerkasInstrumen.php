<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

// Fitur baru "Berkas Instrumen" (13 Sep 2026) - repositori link dokumen pendukung instrumen
// (nama, keterangan, link - biasanya Google Drive). Halaman Admin only.
class BerkasInstrumen extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'berkas_instrumen';

    protected $fillable = [
        'nama',
        'keterangan',
        'link',
        // Digabung (16 Sep 2026) ke kategori yang SAMA dengan halaman Instrumen - sebelumnya
        // 'kategori_berkas_instrumen_id' (tabel sendiri), sekarang 'kategori_instrumen_id',
        // lihat migration gabungkan_kategori_berkas_instrumen_ke_kategori_instrumen.
        'kategori_instrumen_id',
    ];

    public function kategoriInstrumen()
    {
        return $this->belongsTo(KategoriInstrumen::class, 'kategori_instrumen_id');
    }
}
