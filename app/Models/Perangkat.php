<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perangkat extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode',
        'nama',
        'sn',
        'tipe',
        'lokasi',
        'ip_address',
        'mac_address',
        'icon',
    ];
}
