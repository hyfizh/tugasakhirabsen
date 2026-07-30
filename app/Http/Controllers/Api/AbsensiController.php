<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\Absensi;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi Input Payload dari Alat / Python
        $request->validate([
            'uid_rfid' => 'nullable|string',
            'rfid'     => 'nullable|string',
        ]);

        $uid = strtoupper($request->input('uid_rfid') ?? $request->input('rfid') ?? '');

        if (empty($uid)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data RFID tidak boleh kosong!'
            ], 422);
        }

        // 2. Deteksi Otomatis Seluruh Kolom di Tabel mahasiswas
        $columns = Schema::getColumnListing('mahasiswas');
        $rfidColumn = null;

        // Daftar kemungkinan nama kolom RFID
        $candidateColumns = ['rfid', 'uid_rfid', 'no_rfid', 'uid', 'nomor_kartu', 'card_id', 'rfid_tag', 'id_card', 'kartu_id', 'card_uid'];

        foreach ($candidateColumns as $candidate) {
            if (in_array($candidate, $columns)) {
                $rfidColumn = $candidate;
                break;
            }
        }

        // Jika belum ketemu, cari kolom apapun yang mengandung kata 'rfid', 'card', atau 'kartu'
        if (!$rfidColumn) {
            foreach ($columns as $col) {
                if (str_contains($col, 'rfid') || str_contains($col, 'card') || str_contains($col, 'kartu')) {
                    $rfidColumn = $col;
                    break;
                }
            }
        }

        // Jika tetap tidak ditemukan di tabel mahasiswas
        if (!$rfidColumn) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kolom RFID tidak ditemukan. Kolom yang ada di tabel mahasiswas: ' . implode(', ', $columns)
            ], 422);
        }

        // 3. Cari Mahasiswa Berdasarkan Kolom yang Ditemukan
        $mahasiswa = Mahasiswa::where($rfidColumn, $uid)->first();

        if (!$mahasiswa) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kartu RFID dengan UID (' . $uid . ') tidak terdaftar di sistem!'
            ], 404);
        }

        // 4. Catat Kehadiran ke Database
        $absensi = Absensi::create([
            'mahasiswa_id' => $mahasiswa->id,
            'tanggal'      => Carbon::now()->toDateString(),
            'jam_masuk'    => Carbon::now()->toTimeString(),
            'status'       => 'Hadir'
        ]);

        // 5. Balas dengan JSON Sukses
        return response()->json([
            'status'  => 'success',
            'message' => 'Absen Berhasil!',
            'data'    => [
                'nama'  => $mahasiswa->nama,
                'nim'   => $mahasiswa->nim ?? '-',
                'waktu' => Carbon::now()->format('H:i:s')
            ]
        ], 200);
    }
}