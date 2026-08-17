<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use App\Models\Mahasiswa;
use App\Models\Jadwal;
use App\Models\Absensi;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MqttSubscribe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mqtt:subscribe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscribe to MQTT broker for IoT RFID & Face verification logs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $host = env('MQTT_HOST', '127.0.0.1');
        $port = (int) env('MQTT_PORT', 1883);
        $username = env('MQTT_USERNAME');
        $password = env('MQTT_PASSWORD');
        $topic = env('MQTT_TOPIC', 'absensi/iot');

        $this->info("Connecting to MQTT broker at $host:$port...");

        try {
            $mqtt = new MqttClient($host, $port, 'laravel_mqtt_client');
            
            $settings = (new ConnectionSettings)
                ->setKeepAliveInterval(60)
                ->setLastWillTopic($topic . '/status')
                ->setLastWillMessage('offline')
                ->setLastWillQualityOfService(1);

            if ($username) {
                $settings->setUsername($username);
            }
            if ($password) {
                $settings->setPassword($password);
            }

            $mqtt->connect($settings, true);
            $this->info("Connected successfully. Subscribing to topic: $topic");

            $mqtt->subscribe($topic, function (string $topic, string $message) {
                $this->info("Received message on topic [$topic]: $message");
                $this->processPayload($message);
            }, 0);

            // Run loop to listen indefinitely
            $mqtt->loop(true);

        } catch (\Exception $e) {
            $this->error("MQTT Subscriber Error: " . $e->getMessage());
            Log::error("MQTT Error: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Process incoming MQTT JSON payload.
     */
    private function processPayload(string $message)
    {
        try {
            $payload = json_decode($message, true);
            if (!$payload || !isset($payload['tipe_log'])) {
                $this->warn("Payload invalid or missing 'tipe_log'.");
                return;
            }

            $tipeLog = $payload['tipe_log'];
            $uid = $payload['rfid_uid'] ?? null;

            // Scenario 1: Temp scan handler for card registration page
            if ($tipeLog === 'RFID_TEMP_SCAN' && $uid) {
                Cache::put('temp_rfid_uid', $uid, 300);
                
                AuditLog::create([
                    'tipe_log' => 'RFID_TEMP_SCAN',
                    'deskripsi' => "Tapping kartu RFID baru via MQTT: $uid",
                    'ip_address' => 'MQTT Broker',
                ]);
                $this->info("Cached temp RFID: $uid");
                return;
            }

            // Scenario 2: Access granted / attendance check-in
            if ($tipeLog === 'ACCESS_GRANTED') {
                $mahasiswaId = $payload['mahasiswa_id'] ?? null;
                $rfidUid = $payload['rfid_uid'] ?? $payload['uid'] ?? null;

                $mahasiswa = null;
                if ($mahasiswaId) {
                    $mahasiswa = Mahasiswa::find($mahasiswaId);
                } elseif ($rfidUid) {
                    $mahasiswa = Mahasiswa::where('rfid_uid', $rfidUid)->first();
                }

                if (!$mahasiswa) {
                    $this->warn("Mahasiswa tidak ditemukan untuk payload ACCESS_GRANTED.");
                    return;
                }

                $jadwalId = $payload['jadwal_id'] ?? null;
                if (!$jadwalId) {
                    $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    $todayName = $days[date('w')];
                    $jadwal = Jadwal::where('kelas_id', $mahasiswa->kelas_id)->where('hari', $todayName)->first()
                        ?? Jadwal::where('kelas_id', $mahasiswa->kelas_id)->first();
                    $jadwalId = $jadwal?->id;
                }

                if (!$jadwalId) {
                    $this->warn("Jadwal tidak ditemukan untuk kelas mahasiswa ID: {$mahasiswa->id}");
                    return;
                }

                $absensi = Absensi::updateOrCreate(
                    [
                        'mahasiswa_id' => $mahasiswa->id,
                        'jadwal_id'    => $jadwalId,
                        'tanggal'      => date('Y-m-d'),
                    ],
                    [
                        'jam_pelajaran_ke'       => 1,
                        'status'                 => 'H',
                        'waktu_tap_rfid'         => now(),
                        'waktu_verifikasi_wajah' => now(),
                    ]
                );

                // Kirim email notifikasi presensi berhasil jika email mahasiswa terverifikasi
                $emailTarget = $mahasiswa->email ?? $mahasiswa->user?->email;
                $isVerified  = $mahasiswa->user && $mahasiswa->user->email_verified_at !== null;

                if (!empty($emailTarget) && $isVerified) {
                    try {
                        $jadwalObj = Jadwal::with('mataKuliah')->find($jadwalId);
                        \Illuminate\Support\Facades\Mail::to($emailTarget)->send(new \App\Mail\AbsenSuksesMail($mahasiswa, $absensi, $jadwalObj));
                        $this->info("📧 Email notifikasi absensi berhasil dikirim ke: {$emailTarget}");
                    } catch (\Exception $e) {
                        Log::error("Gagal mengirim email AbsenSuksesMail via MQTT: " . $e->getMessage());
                    }
                }

                AuditLog::create([
                    'tipe_log'   => 'ACCESS_GRANTED',
                    'deskripsi'  => "Akses Diberikan (MQTT): {$mahasiswa->nama_lengkap} ({$mahasiswa->nim}) berhasil absen hadir.",
                    'ip_address' => 'MQTT Broker',
                ]);

                $this->info("✅ ABSENSI BERHASIL DISIMPAN KE DB! Mahasiswa ID: {$mahasiswa->id}");
                $this->info("Attendance recorded for {$mahasiswa->nama_lengkap} [Hadir Tepat Waktu]");
            }
        } catch (\Exception $e) {
            $this->error("Failed to process MQTT payload: " . $e->getMessage());
            Log::error("MQTT Processing Error: " . $e->getMessage());
        }
    }

    /**
     * Get current school lesson hour slot.
     */
    private function getCurrentJamPelajaran(): ?int
    {
        $time = date('H:i');
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

        // Diluar jam perkuliahan resmi
        return null;
    }

    /**
     * Hitung Status Kehadiran (Hadir = Tepat Waktu, T = Terlambat > 15 Menit)
     */
    private function calculateAttendanceStatus(int $jamPelajaran): string
    {
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

        // Jika tap berada di Window 15 Menit Awal (07:15 - 07:45 untuk jam 07:30) ATAU Window 15 Menit Akhir
        if (($time >= $earlyStart && $time <= $earlyEnd) || ($time >= $lateStart && $time <= $lateEnd)) {
            return 'H'; // Hadir Tepat Waktu / Presensi Valid Sesi
        }

        // Jika lewat dari 15 menit awal (misal lewat dari 07:45), langsung dicatat Alpa ('A')
        return 'A'; // Alpa langsung
    }
}
