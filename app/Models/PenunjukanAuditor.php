<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PenunjukanAuditor extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'penunjukan_auditors';
    protected $primaryKey = 'id';
    
    // WAJIB: Ganti 'semester' jadi 'jadwal_spmi_id'
    protected $fillable = [
        'dosen_id', 
        'status', 
        'jadwal_spmi_id'
    ];

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    // Relasi balik ke Jadwal Audit
    public function jadwalAudit()
    {
        return $this->belongsTo(JadwalAudit::class, 'jadwal_spmi_id');
    }
}