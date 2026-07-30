<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StrukturAnggota extends Model
{
    protected $table = "struktur_anggota";

    protected $fillable = [
        'nama',
        'jabatan',
        'status',
        'tugas',
        'foto'
    ];
}
