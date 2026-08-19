<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpThreshold extends Model
{
    use HasFactory;

    protected $table = 'sp_thresholds';

    protected $fillable = [
        'sp_level',
        'min_alpha',
        'judul_sp',
        'is_active',
    ];
}
