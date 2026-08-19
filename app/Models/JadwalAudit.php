<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class JadwalAudit extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'jadwal_spmi';
    protected $primaryKey = 'id';
    protected $fillable = [
        
        'tanggal_awal',
        'tanggal_akhir',
        'semester',
        'nama_jadwal',
        'area_audit',
    ];

    // Relasi ke tabel penunjukan_auditors (WAJIB pakai 'jadwal_spmi_id')
    public function penunjukan()
    {
        return $this->hasMany(PenunjukanAuditor::class, 'jadwal_spmi_id');
    }

    public function listPertanyaan()
    {
        return $this->hasMany(ListPertanyaan::class, 'jadwal_id');
    }
}