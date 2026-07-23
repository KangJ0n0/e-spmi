<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Dosen extends Model
{


    /** @use HasFactory<\Database\Factories\DosenFactory> */
    use hasUuids;


    protected $table = 'dosen';
    protected $primaryKey = 'id';
    protected $fillable = ['user_id', 'prodi', 'nama_dosen', 'nidn', 'gelar_depan', 'gelar_belakang', 'email_dosen', 'no_wa', 'foto_dosen'];

}