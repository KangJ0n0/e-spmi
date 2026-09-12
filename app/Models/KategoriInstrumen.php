<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class KategoriInstrumen extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'kategori_instrumen';

    protected $fillable = [
        'nama',
    ];

    public function bankPertanyaan()
    {
        return $this->hasMany(BankPertanyaan::class, 'kategori_instrumen_id');
    }
}
