<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class BankPertanyaan extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'bank_pertanyaans';

    protected $fillable = [
        'pertanyaan',
        'butir_pertanyaan',
        'dokumen_cek',
        'kategori_instrumen_id',
        'urutan',
    ];

    public function kategoriInstrumen()
    {
        return $this->belongsTo(KategoriInstrumen::class, 'kategori_instrumen_id');
    }
}