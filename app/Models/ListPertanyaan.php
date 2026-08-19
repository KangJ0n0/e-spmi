<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ListPertanyaan extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'list_pertanyaans';

    protected $fillable = [
        'jadwal_id',
        'pertanyaan_id',
        'status_jawaban',
    ];

    // Relasi ke soal/instrumen di BankPertanyaan
    public function pertanyaan()
    {
        return $this->belongsTo(BankPertanyaan::class, 'pertanyaan_id');
    }

    // Relasi ke model JadwalAudit (yang menunjuk ke tabel jadwal_spmi)
    public function jadwalAudit()
    {
        return $this->belongsTo(JadwalAudit::class, 'jadwal_id');
    }
}