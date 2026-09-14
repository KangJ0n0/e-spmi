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
        'kategori_berkas_instrumen_id',
    ];

    public function kategoriBerkasInstrumen()
    {
        return $this->belongsTo(KategoriBerkasInstrumen::class, 'kategori_berkas_instrumen_id');
    }
}
