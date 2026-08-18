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

        // Aturan Presensi:
        // Window Hadir Tepat Waktu (H): 15 menit sebelum matkul dimulai s/d 15 menit sesudah matkul dimulai
        // Contoh Matkul 07:30 -> Hadir (H) dari 07:15 s/d 07:45
        $windowStart = date('H:i', strtotime($startTime . ' - 15 minutes'));
        $windowEnd   = date('H:i', strtotime($startTime . ' + 15 minutes'));

        if ($time >= $windowStart && $time <= $windowEnd) {
            return 'H'; // Hadir (H)
        }

        // Jika tap lewat dari 15 menit setelah matkul dimulai (> 07:45) -> Status Terlambat (T)
        if ($time > $windowEnd) {
            return 'T'; // Terlambat (T)
        }

        return 'H';
    }

    public function heartbeat(Request $request)
    {
        $kode = $request->input('kode_perangkat', $request->input('kode'));

        if ($kode) {
            $perangkat = \App\Models\Perangkat::where('kode', trim($kode))->first();
            if ($perangkat) {
                $perangkat->update(['last_seen_at' => now()]);
            } else {
                \App\Models\Perangkat::query()->update(['last_seen_at' => now()]);
            }
        } else {
            \App\Models\Perangkat::query()->update(['last_seen_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Heartbeat IoT diterima & dicatat.',
            'status' => 'Online',
            'timestamp' => now()->toIso8601String()
        ]);
    }

    public function verify(Request $request)
    {
        // Touch device last_seen_at timestamp
        \App\Models\Perangkat::query()->update(['last_seen_at' => now()]);

        $request->validate([
            'rfid_uid' => 'required|string',
        ]);

        $uid = trim($request->rfid_uid);

        try {
            // 1. Find Mahasiswa
            $mahasiswa = Mahasiswa::where('rfid_uid', $uid)->first();
            if (!$mahasiswa) {
                // Cache UID for 60 seconds so the Admin Web interface auto-populates it!
                Cache::put('temp_rfid_uid', $uid, 60);

                AuditLog::create([
                    'tipe_log' => 'RFID_SCANNED',
                    'deskripsi' => "Kartu RFID baru di-tap: $uid (Menunggu Pendaftaran ke Mahasiswa)",
                    'ip_address' => $request->ip(),
                ]);

                return response()->json([
                    'status' => 'unregistered',
                    'scanned_uid' => $uid,
                    'message' => 'Kartu RFID baru terdeteksi! Kode UID tersimpan sementara untuk pendaftaran.',
                ], 200);
            }

            // 2. Determine Day and Time Slot
            $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            $todayName = $days[date('w')];
            if ($todayName == 'Minggu') {
                $todayName = 'Senin'; // fallback to Monday for testing
            }

            $currentJam = $this->getCurrentJamPelajaran() ?? 1;

            // 3. Find Schedule for Student's Class (Fallback to any class schedule if not exact time)
            $jadwal = Jadwal::where('kelas_id', $mahasiswa->kelas_id)
                ->where('hari', $todayName)
                ->first()
                ?? Jadwal::where('kelas_id', $mahasiswa->kelas_id)->first();

            $mataKuliahNama = ($jadwal && $jadwal->mataKuliah) ? $jadwal->mataKuliah->nama_mk : 'Mata Kuliah Umum';
            $jadwalId = $jadwal ? $jadwal->id : 1;

            AuditLog::create([
                'tipe_log' => 'RFID_VERIFIED',
                'deskripsi' => "Tapping RFID Valid: {$mahasiswa->nama_lengkap} ({$mahasiswa->nim}) - Matkul: {$mataKuliahNama}",
                'ip_address' => $request->ip(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => "Validasi RFID Berhasil! Pemilik: {$mahasiswa->nama_lengkap}",
                'mahasiswa' => [
                    'id' => $mahasiswa->id,
                    'nim' => $mahasiswa->nim,
                    'nama_lengkap' => $mahasiswa->nama_lengkap,
                    'foto_wajah' => $mahasiswa->foto_wajah ? asset('storage/' . $mahasiswa->foto_wajah) : null,
                ],
                'jadwal' => [
                    'id' => $jadwalId,
                    'mata_kuliah' => $mataKuliahNama,
                ],
                'jam_pelajaran' => $currentJam,
            ], 200);
        } catch (\Exception $e) {
            Log::error("IoT Verify Exception: " . $e->getMessage(), [
                'exception' => $e,
                'rfid_uid' => $uid
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error backend: ' . $e->getMessage(),
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function log(Request $request)
    {
        $request->validate([
            'tipe_log'     => 'required|string',
            'deskripsi'    => 'nullable|string',
            'rfid_uid'     => 'nullable|string',
            'mahasiswa_id' => 'nullable|integer',
            'jadwal_id'    => 'nullable|integer',
        ]);

        $tipeLog   = $request->tipe_log;
        $uid       = $request->rfid_uid;
        $deskripsi = $request->input('deskripsi', "Aktivitas IoT: $tipeLog ($uid)");

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
                $uid = $request->rfid_uid;
                $mahasiswa = $request->mahasiswa_id 
                    ? Mahasiswa::find($request->mahasiswa_id) 
                    : ($uid ? Mahasiswa::where('rfid_uid', $uid)->first() : null);

                if (!$mahasiswa) {
                    AuditLog::create([
                        'tipe_log' => 'ACCESS_DENIED',
                        'deskripsi' => "Akses Ditolak: Kartu RFID $uid belum terdaftar pada mahasiswa mana pun.",
                        'ip_address' => $request->ip(),
                    ]);

                    return response()->json([
                        'status' => 'error',
                        'message' => 'Kartu RFID belum terdaftar pada mahasiswa mana pun.',
                    ], 404);
                }

                $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                $todayName = $days[date('w')];
                $currentJam = $this->getCurrentJamPelajaran() ?? 1;

                $jadwal = $request->jadwal_id 
                    ? Jadwal::find($request->jadwal_id) 
                    : (Jadwal::where('kelas_id', $mahasiswa->kelas_id)->where('hari', $todayName)->first() 
                       ?? Jadwal::where('kelas_id', $mahasiswa->kelas_id)->first());

                $jadwalId = $jadwal ? $jadwal->id : 1;

                $calculatedStatus = $this->calculateAttendanceStatus($currentJam);

                $absensi = Absensi::updateOrCreate(
                    [
                        'mahasiswa_id' => $mahasiswa->id,
                        'jadwal_id'    => $jadwalId,
                        'tanggal'      => date('Y-m-d'),
                    ],
                    [
                        'jam_pelajaran_ke'       => $currentJam,
                        'status'                 => $calculatedStatus,
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

                AuditLog::create([
                    'tipe_log'   => 'ACCESS_GRANTED',
                    'deskripsi'  => "Akses Diberikan: {$mahasiswa->nama_lengkap} ({$mahasiswa->nim}) berhasil absen Hadir Tepat Waktu.",
                    'ip_address' => $request->ip(),
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Kehadiran berhasil disimpan (Hadir).',
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
