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
        $time = \Carbon\Carbon::now('Asia/Jakarta')->format('H:i');
        
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

    private function calculateAttendanceStatus(?int $currentJam, ?Jadwal $jadwal = null): string
    {
        $time = \Carbon\Carbon::now('Asia/Jakarta')->format('H:i');
        
        $sessionStartTimes = [
            1  => '07:30', 2  => '08:20', 3  => '09:10', 4  => '10:10', 5  => '11:00',
            6  => '11:50', 7  => '13:30', 8  => '14:20', 9  => '15:10', 10 => '16:00',
        ];

        // Gunakan slot jam pelajaran saat ini ($currentJam) sebagai acuan batas waktu presensi
        $jamIndex = $currentJam ?? ($jadwal && $jadwal->jam_mulai ? (int)$jadwal->jam_mulai : 1);
        $startTime = $sessionStartTimes[$jamIndex] ?? '07:30';

        // Aturan Presensi:
        // Window Hadir Tepat Waktu (H): 15 menit sebelum jam matkul s/d 15 menit sesudah jam matkul
        // Contoh Jam ke-4 (10:10) -> Hadir Tepat Waktu (H) dari 09:55 s/d 10:25
        $windowStart = date('H:i', strtotime($startTime . ' - 15 minutes'));
        $windowEnd   = date('H:i', strtotime($startTime . ' + 15 minutes'));

        if ($time >= $windowStart && $time <= $windowEnd) {
            return 'H'; // Hadir Tepat Waktu (H)
        }

        // STRICT POLICY: Tidak ada status Terlambat (T). Jika tap lewat > 15m -> DENIED (Akses Ditolak)
        return 'DENIED';
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

            // Check if student has registered face photo
            if (empty($mahasiswa->foto_wajah)) {
                AuditLog::create([
                    'tipe_log' => 'ACCESS_DENIED',
                    'deskripsi' => "Presensi Ditolak: Mahasiswa {$mahasiswa->nama_lengkap} ({$mahasiswa->nim}) BELUM mendaftarkan foto wajah profil di database.",
                    'ip_address' => $request->ip(),
                ]);

                return response()->json([
                    'status' => 'face_missing',
                    'message' => "Presensi Gagal! Mahasiswa {$mahasiswa->nama_lengkap} BELUM mengunggah/mendaftarkan foto profil wajah di database web.",
                    'mahasiswa' => [
                        'id'           => $mahasiswa->id,
                        'nim'          => $mahasiswa->nim,
                        'nama_lengkap' => $mahasiswa->nama_lengkap,
                        'foto_wajah'   => null,
                    ]
                ], 200);
            }

            // 2. Determine Day and Time Slot using Carbon (Asia/Jakarta)
            $now = \Carbon\Carbon::now('Asia/Jakarta');
            $currentTime = $now->format('H:i');

            $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            $todayName = $days[$now->dayOfWeek];
            if ($todayName == 'Minggu') {
                $todayName = 'Senin'; // fallback for testing
            }

            $currentJam = $this->getCurrentJamPelajaran() ?? 1;

            // 3. Find Schedule for Student's Class
            $jadwal = Jadwal::where('kelas_id', $mahasiswa->kelas_id)
                ->where('hari', $todayName)
                ->first()
                ?? Jadwal::where('kelas_id', $mahasiswa->kelas_id)->first();

            $sessionEndTimes = [
                1  => '08:20', 2  => '09:10', 3  => '10:00', 4  => '11:00', 5  => '11:50',
                6  => '12:40', 7  => '14:20', 8  => '15:10', 9  => '16:00', 10 => '16:50',
            ];

            // Check if schedule for today has ALREADY EXPIRED (Current Time > Schedule End Time)
            if ($jadwal) {
                $jamEndIndex = (int) ($jadwal->jam_selesai ?? 4);
                $endTime = $sessionEndTimes[$jamEndIndex] ?? '16:50';

                if ($currentTime > $endTime) {
                    AuditLog::create([
                        'tipe_log'   => 'ACCESS_DENIED',
                        'deskripsi'  => "Presensi Ditolak: Sesi perkuliahan hari ini ($todayName) sudah berakhir untuk {$mahasiswa->nama_lengkap} ({$mahasiswa->nim}).",
                        'ip_address' => $request->ip(),
                    ]);

                    return response()->json([
                        'status'  => 'schedule_not_found',
                        'message' => 'Sesi perkuliahan sudah berakhir',
                        'mahasiswa' => [
                            'id'           => $mahasiswa->id,
                            'nim'          => $mahasiswa->nim,
                            'nama_lengkap' => $mahasiswa->nama_lengkap,
                        ]
                    ], 200);
                }
            }

            $mataKuliahNama = ($jadwal && $jadwal->mataKuliah) ? $jadwal->mataKuliah->nama_mk : 'Mata Kuliah Umum';
            $ruanganNama    = ($jadwal && $jadwal->ruangan) ? $jadwal->ruangan : 'Lab IoT';
            $kelasNama      = ($mahasiswa->kelas) ? $mahasiswa->kelas->nama_kelas : 'TI-3A';
            $jadwalId       = $jadwal ? $jadwal->id : 1;
            
            $statusPresensiCode  = $this->calculateAttendanceStatus($currentJam, $jadwal);

            if ($statusPresensiCode === 'DENIED') {
                AuditLog::create([
                    'tipe_log' => 'ACCESS_DENIED',
                    'deskripsi' => "Akses Ditolak: Mahasiswa {$mahasiswa->nama_lengkap} ({$mahasiswa->nim}) mencoba tap di luar jendela toleransi presensi (Terlambat > 15 Menit).",
                    'ip_address' => $request->ip(),
                ]);

                return response()->json([
                    'status' => 'time_expired',
                    'message' => "Presensi Ditolak! Mahasiswa {$mahasiswa->nama_lengkap} tap di luar batas toleransi (Terlambat > 15 Menit).",
                    'mahasiswa' => [
                        'id'           => $mahasiswa->id,
                        'nim'          => $mahasiswa->nim,
                        'nama_lengkap' => $mahasiswa->nama_lengkap,
                    ]
                ], 200);
            }

            $statusPresensiLabel = 'HADIR TEPAT WAKTU (H) 🟢';

            AuditLog::create([
                'tipe_log' => 'RFID_VERIFIED',
                'deskripsi' => "Tapping RFID Valid: {$mahasiswa->nama_lengkap} ({$mahasiswa->nim}) - Kelas: {$kelasNama} - Matkul: {$mataKuliahNama}",
                'ip_address' => $request->ip(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => "Validasi RFID Berhasil! Pemilik: {$mahasiswa->nama_lengkap}",
                'mahasiswa' => [
                    'id'           => $mahasiswa->id,
                    'nim'          => $mahasiswa->nim,
                    'nama_lengkap' => $mahasiswa->nama_lengkap,
                    'kelas'        => $kelasNama,
                    'foto_wajah'   => $mahasiswa->foto_wajah ? asset('storage/' . $mahasiswa->foto_wajah) : null,
                ],
                'jadwal' => [
                    'id'          => $jadwalId,
                    'mata_kuliah' => $mataKuliahNama,
                    'ruangan'     => $ruanganNama,
                    'hari'        => $todayName,
                    'jam_mulai'   => $jadwal ? $jadwal->jam_mulai : 1,
                    'jam_selesai' => $jadwal ? $jadwal->jam_selesai : 4,
                    'dosen'       => ($jadwal && $jadwal->dosen) ? $jadwal->dosen->nama_dosen : 'Dosen Pengampu',
                ],
                'jam_pelajaran'         => $currentJam,
                'status_presensi'       => $statusPresensiCode,
                'status_presensi_label' => $statusPresensiLabel,
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

            // Route: Log access denied / face verification failed
            $wajahValid = $request->boolean('wajah_valid', true);

            if ($tipeLog === 'ACCESS_DENIED' || !$wajahValid) {
                $uid = $request->rfid_uid;
                $mahasiswa = $request->mahasiswa_id 
                    ? Mahasiswa::find($request->mahasiswa_id) 
                    : ($uid ? Mahasiswa::where('rfid_uid', $uid)->first() : null);

                $namaMhs = $mahasiswa ? "{$mahasiswa->nama_lengkap} ({$mahasiswa->nim})" : "UID: $uid";

                AuditLog::create([
                    'tipe_log' => 'ACCESS_DENIED',
                    'deskripsi' => "Akses Ditolak & Presensi Gagal: Verifikasi Biometrik Wajah TIDAK COCOK dengan $namaMhs.",
                    'ip_address' => $request->ip(),
                ]);

                return response()->json([
                    'status' => 'denied',
                    'message' => 'Presensi Ditolak! Wajah di depan kamera TIDAK COCOK dengan foto profil terdaftar.',
                ], 403);
            }

            // Route: Log access granted / verification results (ONLY IF WAJAH VALID)
            if ($tipeLog === 'ACCESS_GRANTED' && $wajahValid) {
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
                if ($todayName == 'Minggu') {
                    $todayName = 'Senin';
                }
                $currentJam = $this->getCurrentJamPelajaran() ?? 1;

                $jadwal = $request->jadwal_id 
                    ? Jadwal::find($request->jadwal_id) 
                    : (Jadwal::where('kelas_id', $mahasiswa->kelas_id)->where('hari', $todayName)->first() 
                       ?? Jadwal::where('kelas_id', $mahasiswa->kelas_id)->first());

                if (!$jadwal) {
                    $firstMk = \App\Models\MataKuliah::first();
                    $jadwal = Jadwal::firstOrCreate(
                        [
                            'kelas_id' => $mahasiswa->kelas_id,
                            'hari'     => $todayName,
                        ],
                        [
                            'mata_kuliah_id' => $firstMk ? $firstMk->id : 1,
                            'dosen_id'       => 1,
                            'jam_mulai'      => 1,
                            'jam_selesai'    => 2,
                            'ruangan'        => 'Lab IoT',
                        ]
                    );
                }

                $jadwalId = $jadwal ? $jadwal->id : 1;

                $calculatedStatus = $this->calculateAttendanceStatus($currentJam, $jadwal);

                $jamStart = ($jadwal && $jadwal->jam_mulai) ? (int)$jadwal->jam_mulai : 1;
                $jamEnd   = ($jadwal && $jadwal->jam_selesai) ? (int)$jadwal->jam_selesai : 4;

                // Jika jam_selesai sama dengan atau lebih kecil dari jam_mulai, perluas ke blok 4 jam matkul (misal Jam 1-4)
                if ($jamEnd <= $jamStart) {
                    $jamEnd = $jamStart + 3;
                }
                if ($jamEnd > 10) {
                    $jamEnd = 10;
                }

                $lastAbsensi = null;
                for ($j = $jamStart; $j <= $jamEnd; $j++) {
                    $lastAbsensi = Absensi::updateOrCreate(
                        [
                            'mahasiswa_id'     => $mahasiswa->id,
                            'jadwal_id'        => $jadwalId,
                            'tanggal'          => date('Y-m-d'),
                            'jam_pelajaran_ke' => $j,
                        ],
                        [
                            'status'                 => $calculatedStatus,
                            'waktu_tap_rfid'         => date('Y-m-d H:i:s'),
                            'waktu_verifikasi_wajah' => date('Y-m-d H:i:s'),
                        ]
                    );
                }
                $absensi = $lastAbsensi;

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
