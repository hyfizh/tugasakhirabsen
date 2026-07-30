<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensis';

    protected $fillable = [
        'mahasiswa_id',
        'jadwal_id',
        'tanggal',
        'jam_pelajaran_ke',
        'status',
        'waktu_tap_rfid',
        'waktu_verifikasi_wajah',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu_tap_rfid' => 'datetime',
        'waktu_verifikasi_wajah' => 'datetime',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class);
    }
}
