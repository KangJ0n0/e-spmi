<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KonfigurasiNomorDokumen extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'konfigurasi_nomor_dokumen';

    protected $fillable = [
        'semester',
        'nomor_dokumen_1',
        'nomor_dokumen_2',
        'nomor_dokumen_3',
        'nomor_dokumen_4',
        'nomor_dokumen_5',
        'nomor_dokumen_6',
    ];
}
