<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nim',
        'nama_lengkap',
        'rfid_uid',
        'foto_wajah',
        'last_photo_updated_at',
        'email',
        'no_hp',
        'kelas_id',
    ];

    protected $casts = [
        'last_photo_updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function absensis()
    {
        return $this->hasMany(Absensi::class);
    }

    public function suratPeringatans()
    {
        return $this->hasMany(SuratPeringatan::class);
    }

    public function permohonanGantiFotos()
    {
        return $this->hasMany(PermohonanGantiFoto::class, 'mahasiswa_id');
    }
}
