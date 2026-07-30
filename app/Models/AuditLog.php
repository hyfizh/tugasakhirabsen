<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    const UPDATED_AT = null;

    protected $fillable = [
        'tipe_log',
        'deskripsi',
        'ip_address',
        'created_at',
    ];
}
