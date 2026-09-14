<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

// Kategori khusus "Berkas Instrumen" (13 Sep 2026) - TERPISAH dari KategoriInstrumen (yang
// dipakai Bank Pertanyaan), dikonfirmasi user. Struktur & pola sama persis.
class KategoriBerkasInstrumen extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'kategori_berkas_instrumen';

    protected $fillable = [
        'nama',
    ];

    public function berkasInstrumen()
    {
        return $this->hasMany(BerkasInstrumen::class, 'kategori_berkas_instrumen_id');
    }
}
