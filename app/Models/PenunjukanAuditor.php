<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PenunjukanAuditor extends Model
{
    /** @use HasFactory<\Database\Factories\PenunjukanAuditorFactory> */
    use HasUuids;

    protected $table = 'penunjukan_auditors';
    protected $primaryKey = 'id';
    protected $fillable = ['dosen_id', 'status', 'semester'];
}
