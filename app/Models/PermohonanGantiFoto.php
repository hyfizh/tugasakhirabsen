<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermohonanGantiFoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'mahasiswa_id',
        'alasan',
        'status',
        'admin_note',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }
}
