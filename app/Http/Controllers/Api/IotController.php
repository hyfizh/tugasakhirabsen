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
use Illuminate\Support\Facades\Mail;
use App\Mail\AbsenSuksesMail;

class IotController extends Controller
{
    private function getCurrentJamPelajaran(): ?int
    {
        $time = date('H:i');
        
        // Define lesson hours mapping (Jam 1: Kelas mulai 07:30, dibuka tap awal dari 07:00)
        $hours = [
            1  => ['07:00', '08:20'],
            2  => ['08:20', '09:10'],
            3  => ['09:10', '10:00'],
            4  => ['10:10', '11:00'],
            5  => ['11:00', '11:50'],
            6  => ['11:50', '12:40'],
            7  => ['13:30', '14:20'],
            8  => ['14:20', '15:10'],
            9  => ['15:10', '16:00'],
            10 => ['16:00', '16:50'],
        ];

        foreach ($hours as $slot => $range) {
            if ($time >= $range[0] && $time <= $range[1]) {
                return $slot;
            }
        }

        // Diluar jam perkuliahan resmi (07:00 - 16:50 WIB)
        return null;
    }

    private function calculateAttendanceStatus(?int $jamPelajaran): string
    {
        if (!$jamPelajaran) return 'H';

        $time = date('H:i');
        $sessionTimes = [
            1  => ['start' => '07:30', 'end' => '08:20'],
            2  => ['start' => '08:20', 'end' => '09:10'],
            3  => ['start' => '09:10', 'end' => '10:00'],
            4  => ['start' => '10:10', 'end' => '11:00'],
            5  => ['start' => '11:00', 'end' => '11:50'],
            6  => ['start' => '11:50', 'end' => '12:40'],
            7  => ['start' => '13:30', 'end' => '14:20'],
            8  => ['start' => '14:20', 'end' => '15:10'],
            9  => ['start' => '15:10', 'end' => '16:00'],
            10 => ['start' => '16:00', 'end' => '16:50'],
        ];

        if (!isset($sessionTimes[$jamPelajaran])) {
            return 'H';
        }

        $startTime = $sessionTimes[$jamPelajaran]['start'];
        $endTime   = $sessionTimes[$jamPelajaran]['end'];

        // 1. Window 15 Menit Awal (Dimulai 15 menit sebelum jam kelas sampai 15 menit setelah kelas mulai)
        $earlyStart = date('H:i', strtotime($startTime . ' - 15 minutes'));
        $earlyEnd   = date('H:i', strtotime($startTime . ' + 15 minutes'));

        // 2. Window 15 Menit Akhir (Dimulai 15 menit sebelum kelas selesai sampai 15 menit setelah kelas selesai)
        $lateStart  = date('H:i', strtotime($endTime . ' - 15 minutes'));
        $lateEnd    = date('H:i', strtotime($endTime . ' + 15 minutes'));

        // Jika tap berada di Window 15 Menit Awal ATAU Window 15 Menit Akhir
        if (($time >= $earlyStart && $time <= $earlyEnd) || ($time >= $lateStart && $time <= $lateEnd)) {
            return 'H'; // Hadir Tepat Waktu / Presensi Valid Sesi
        }

        // Jika tap di tengah-tengah rentang waktu perkuliahan (> 15 menit awal & < 15 menit akhir)
        return 'T'; // Terlambat
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
                $jadwal = Jadwal::find($request->jadwal_id);
                $currentJam = $this->getCurrentJamPelajaran();

                // Check existing record for today
                $existingAbsensi = Absensi::where('mahasiswa_id', $mahasiswa->id)
                    ->where('jadwal_id', $request->jadwal_id)
                    ->where('tanggal', date('Y-m-d'))
                    ->first();

                if ($existingAbsensi) {
                    // Retain status 'H' if already recorded on 1st tap
                    $statusKehadiran = $existingAbsensi->status === 'H' ? 'H' : $this->calculateAttendanceStatus($currentJam);
                    $recordedJam = $existingAbsensi->jam_pelajaran_ke;
                } else {
                    // First tap of the day for this subject
                    $recordedJam = $currentJam;
                    // If tapping on 2nd hour or later of a multi-hour subject without tapping on 1st hour
                    if ($jadwal && $currentJam > $jadwal->jam_mulai) {
                        $statusKehadiran = 'T'; // Terlambat (Masuk Jam Kedua)
                    } else {
                        $statusKehadiran = $this->calculateAttendanceStatus($currentJam);
                    }
                }

                $absensi = Absensi::updateOrCreate(
                    [
                        'mahasiswa_id' => $mahasiswa->id,
                        'jadwal_id'    => $request->jadwal_id,
                        'tanggal'      => date('Y-m-d'),
                    ],
                    [
                        'jam_pelajaran_ke'       => $recordedJam,
                        'status'                 => $statusKehadiran,
                        'waktu_tap_rfid'         => date('Y-m-d H:i:s'),
                        'waktu_verifikasi_wajah' => date('Y-m-d H:i:s'),
                    ]
                );

                // Kirim email notifikasi presensi berhasil jika email terverifikasi
                $emailTarget = $mahasiswa->email ?? $mahasiswa->user?->email;
                $isVerified  = $mahasiswa->user && $mahasiswa->user->email_verified_at !== null;

                if (!empty($emailTarget) && $isVerified) {
                    try {
                        Mail::to($emailTarget)->send(new AbsenSuksesMail($mahasiswa, $absensi, $jadwal));
                    } catch (\Exception $e) {
                        Log::error("Gagal mengirim email AbsenSuksesMail via API: " . $e->getMessage());
                    }
                }

                $statusLabel = $statusKehadiran === 'T' ? ($jadwal && $currentJam > $jadwal->jam_mulai ? 'Terlambat (Masuk Jam ke-' . $currentJam . ')' : 'Terlambat') : 'Hadir Tepat Waktu';
                AuditLog::create([
                    'tipe_log'   => 'ACCESS_GRANTED',
                    'deskripsi'  => "Akses Diberikan: {$mahasiswa->nama_lengkap} ({$mahasiswa->nim}) berhasil absen ($statusLabel) pada mata kuliah.",
                    'ip_address' => $request->ip(),
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Kehadiran berhasil disimpan (' . $statusLabel . ').',
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
