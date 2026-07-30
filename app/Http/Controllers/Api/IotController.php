<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Jadwal;
use App\Models\Absensi;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class IotController extends Controller
{
    private function getCurrentJamPelajaran(): ?int
    {
        $time = date('H:i');
        
        // Define lesson hours mapping
        $hours = [
            1  => ['07:00', '07:50'],
            2  => ['07:50', '08:40'],
            3  => ['08:40', '09:30'],
            4  => ['09:40', '10:30'],
            5  => ['10:30', '11:20'],
            6  => ['11:20', '12:10'],
            7  => ['12:30', '13:20'],
            8  => ['13:20', '14:10'],
            9  => ['14:10', '15:00'],
            10 => ['15:00', '15:50'],
        ];

        foreach ($hours as $slot => $range) {
            if ($time >= $range[0] && $time <= $range[1]) {
                return $slot;
            }
        }

        // Default fallback for testing or manual simulation:
        // If outside normal hours, let's return a default lesson hour 1 so it's easily testable
        return 1;
    }

    public function verify(Request $request)
    {
        $request->validate([
            'rfid_uid' => 'required|string',
        ]);

        $uid = $request->rfid_uid;

        try {
            // 1. Find Mahasiswa
            $mahasiswa = Mahasiswa::where('rfid_uid', $uid)->first();
            if (!$mahasiswa) {
                AuditLog::create([
                    'tipe_log' => 'ACCESS_DENIED',
                    'deskripsi' => "Akses Ditolak: Kartu RFID dengan UID $uid tidak terdaftar.",
                    'ip_address' => $request->ip(),
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Kartu RFID tidak dikenal.',
                ], 404);
            }

            // 2. Determine Day and Time Slot
            $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            $todayIndex = date('w');
            $todayName = $days[$todayIndex];
            if ($todayName == 'Minggu') {
                $todayName = 'Senin'; // fallback to Monday for testing
            }

            $currentJam = $this->getCurrentJamPelajaran();

            // 3. Find Schedule for Student's Class
            $jadwal = Jadwal::where('kelas_id', $mahasiswa->kelas_id)
                ->where('hari', $todayName)
                ->where('jam_mulai', '<=', $currentJam)
                ->where('jam_selesai', '>=', $currentJam)
                ->first();

            if (!$jadwal) {
                AuditLog::create([
                    'tipe_log' => 'ACCESS_DENIED',
                    'deskripsi' => "Akses Ditolak: Mahasiswa {$mahasiswa->nama_lengkap} tapping di luar jam kuliah terdaftar.",
                    'ip_address' => $request->ip(),
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada kelas terdaftar saat ini.',
                    'mahasiswa' => [
                        'nama_lengkap' => $mahasiswa->nama_lengkap,
                        'nim' => $mahasiswa->nim,
                    ]
                ], 400);
            }

            // 4. Return success data for camera validation
            return response()->json([
                'status' => 'success',
                'message' => 'Validasi awal RFID berhasil. Silakan posisikan wajah ke kamera.',
                'mahasiswa' => [
                    'id' => $mahasiswa->id,
                    'nim' => $mahasiswa->nim,
                    'nama_lengkap' => $mahasiswa->nama_lengkap,
                    'foto_wajah' => $mahasiswa->foto_wajah ? asset('storage/' . $mahasiswa->foto_wajah) : null,
                ],
                'jadwal' => [
                    'id' => $jadwal->id,
                    'mata_kuliah' => $jadwal->mataKuliah->nama_mk,
                ],
                'jam_pelajaran' => $currentJam,
            ]);
        } catch (\Exception $e) {
            Log::error("IoT Verify Exception: " . $e->getMessage(), [
                'exception' => $e,
                'rfid_uid' => $uid
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Sistem absensi sedang mengalami gangguan database/koneksi.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function log(Request $request)
    {
        $request->validate([
            'tipe_log' => 'required|string',
            'deskripsi' => 'required|string',
            'rfid_uid' => 'nullable|string',
            'mahasiswa_id' => 'nullable|integer',
            'jadwal_id' => 'nullable|integer',
        ]);

        $tipeLog = $request->tipe_log;
        $uid = $request->rfid_uid;

        try {
            // Route: Temp scan handler for card registration page
            if ($tipeLog === 'RFID_TEMP_SCAN') {
                Cache::put('temp_rfid_uid', $uid, 300); // cache for 5 minutes

                AuditLog::create([
                    'tipe_log' => 'RFID_TEMP_SCAN',
                    'deskripsi' => "Tapping kartu RFID baru dengan UID: $uid",
                    'ip_address' => $request->ip(),
                ]);

                // If it's a browser tab mock tool, redirect back to scan page
                if ($request->header('Accept') && str_contains($request->header('Accept'), 'html')) {
                    return redirect()->route('admin.rfid.scan')->with('success', 'Simulasi tapping berhasil! UID terdeteksi: ' . $uid);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Temp RFID UID cached successfully.',
                    'rfid_uid' => $uid,
                ]);
            }

            // Route: Log access granted / verification results
            if ($tipeLog === 'ACCESS_GRANTED') {
                $mahasiswa = Mahasiswa::findOrFail($request->mahasiswa_id);
                $currentJam = $this->getCurrentJamPelajaran();
                
                // Record or update attendance status to 'Hadir' (H)
                $absensi = Absensi::updateOrCreate(
                    [
                        'mahasiswa_id' => $mahasiswa->id,
                        'jadwal_id' => $request->jadwal_id,
                        'tanggal' => date('Y-m-d'),
                    ],
                    [
                        'jam_pelajaran_ke' => $currentJam,
                        'status' => 'H',
                        'waktu_tap_rfid' => date('Y-m-d H:i:s'),
                        'waktu_verifikasi_wajah' => date('Y-m-d H:i:s'),
                    ]
                );

                AuditLog::create([
                    'tipe_log' => 'ACCESS_GRANTED',
                    'deskripsi' => "Akses Diberikan: {$mahasiswa->nama_lengkap} ({$mahasiswa->nim}) berhasil absen hadir pada mata kuliah.",
                    'ip_address' => $request->ip(),
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Kehadiran berhasil disimpan.',
                    'absensi' => $absensi,
                ]);
            }

            // Default audit log creation
            AuditLog::create([
                'tipe_log' => $tipeLog,
                'deskripsi' => $request->deskripsi,
                'ip_address' => $request->ip(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Audit log recorded.',
            ]);
        } catch (\Exception $e) {
            Log::error("IoT Log Exception: " . $e->getMessage(), [
                'exception' => $e,
                'payload' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mencatat log kehadiran ke database.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
