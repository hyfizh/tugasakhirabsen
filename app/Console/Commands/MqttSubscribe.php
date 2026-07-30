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
                $jadwalId = $payload['jadwal_id'] ?? null;

                if (!$mahasiswaId || !$jadwalId) {
                    $this->warn("Missing mahasiswa_id or jadwal_id in payload.");
                    return;
                }

                $mahasiswa = Mahasiswa::findOrFail($mahasiswaId);
                $currentJam = $this->getCurrentJamPelajaran();

                $absensi = Absensi::updateOrCreate(
                    [
                        'mahasiswa_id' => $mahasiswa->id,
                        'jadwal_id' => $jadwalId,
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
                    'deskripsi' => "Akses Diberikan (MQTT): {$mahasiswa->nama_lengkap} ({$mahasiswa->nim}) berhasil absen hadir.",
                    'ip_address' => 'MQTT Broker',
                ]);

                $this->info("Attendance recorded for {$mahasiswa->nama_lengkap}");
            }
        } catch (\Exception $e) {
            $this->error("Failed to process MQTT payload: " . $e->getMessage());
            Log::error("MQTT Processing Error: " . $e->getMessage());
        }
    }

    /**
     * Get current school lesson hour slot.
     */
    private function getCurrentJamPelajaran(): int
    {
        $time = date('H:i');
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
        return 1;
    }
}
