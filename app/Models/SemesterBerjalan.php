<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SemesterBerjalan extends Model
{
    /** @use HasFactory<\Database\Factories\SemesterBerjalanFactory> */
    use HasFactory;
    protected $table = 'semester_berjalans';
    protected $primaryKey = 'id';
    protected $fillable = ['semester_sekarang'];

}
