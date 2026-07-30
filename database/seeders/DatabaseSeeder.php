<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\MataKuliah;
use App\Models\Jadwal;
use App\Models\Absensi;
use App\Models\AuditLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Admin User
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'email' => 'admin@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_password_changed' => true,
            ]
        );

        // 2. Create Dosen User & Profile
        $dosenUser = User::firstOrCreate(
            ['username' => 'dosen'],
            [
                'email' => 'dosen@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'dosen',
                'is_password_changed' => true,
            ]
        );

        $dosen = Dosen::firstOrCreate(
            ['user_id' => $dosenUser->id],
            [
                'nip' => '198001012005011002',
                'nama_dosen' => 'Dr. Budi Santoso, M.T.',
                'no_hp' => '081234567890',
            ]
        );

        // 3. Create Kelas
        $kelasA = Kelas::firstOrCreate(['nama_kelas' => 'Teknologi Informasi 3A']);
        $kelasB = Kelas::firstOrCreate(['nama_kelas' => 'Teknologi Informasi 3B']);
        $kelasC = Kelas::firstOrCreate(['nama_kelas' => 'Teknologi Informasi 3C']);

        // 4. Create Mahasiswa User & Profile
        $mhsUser = User::firstOrCreate(
            ['username' => 'mahasiswa'],
            [
                'email' => 'rafli@student.ac.id',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
                'is_password_changed' => false,
            ]
        );

        $mahasiswa = Mahasiswa::firstOrCreate(
            ['nim' => '2241720001'],
            [
                'user_id' => $mhsUser->id,
                'nama_lengkap' => 'Muhammad Rafli',
                'rfid_uid' => '12A34B56',
                'foto_wajah' => null,
                'email' => 'rafli@student.ac.id',
                'no_hp' => '089876543210',
                'kelas_id' => $kelasA->id,
            ]
        );

        $mhsUser2 = User::firstOrCreate(
            ['username' => 'mahasiswa2'],
            [
                'email' => 'siti@student.ac.id',
                'password' => Hash::make('password_baru'),
                'role' => 'mahasiswa',
                'is_password_changed' => true,
            ]
        );

        $mahasiswa2 = Mahasiswa::firstOrCreate(
            ['nim' => '2241720002'],
            [
                'user_id' => $mhsUser2->id,
                'nama_lengkap' => 'Siti Aminah',
                'rfid_uid' => '87C65D43',
                'foto_wajah' => null,
                'email' => 'siti@student.ac.id',
                'no_hp' => '089876543211',
                'kelas_id' => $kelasA->id,
            ]
        );

        // 5. Create Mata Kuliah
        $mk1 = MataKuliah::firstOrCreate(
            ['kode_mk' => 'MK001'],
            ['nama_mk' => 'Internet of Things (IoT)', 'sks' => 4]
        );

        $mk2 = MataKuliah::firstOrCreate(
            ['kode_mk' => 'MK002'],
            ['nama_mk' => 'Pemrograman Web Lanjut', 'sks' => 3]
        );

        $mk3 = MataKuliah::firstOrCreate(
            ['kode_mk' => 'MK003'],
            ['nama_mk' => 'Kecerdasan Buatan', 'sks' => 3]
        );

        // 6. Create Jadwal
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $todayIndex = date('w');
        $todayName = $days[$todayIndex];
        if ($todayName == 'Minggu') {
            $todayName = 'Senin';
        }

        $jadwal1 = Jadwal::firstOrCreate(
            ['kelas_id' => $kelasA->id, 'mata_kuliah_id' => $mk1->id, 'hari' => $todayName],
            [
                'dosen_id' => $dosen->id,
                'jam_mulai' => 1,
                'jam_selesai' => 4,
                'toleransi_keterlambatan' => 15,
            ]
        );

        $jadwal2 = Jadwal::firstOrCreate(
            ['kelas_id' => $kelasA->id, 'mata_kuliah_id' => $mk2->id, 'hari' => $todayName],
            [
                'dosen_id' => $dosen->id,
                'jam_mulai' => 5,
                'jam_selesai' => 7,
                'toleransi_keterlambatan' => 15,
            ]
        );

        // 7. Seed Audit Logs
        AuditLog::firstOrCreate(
            ['deskripsi' => 'Tapping kartu RFID baru dengan UID: 9A:2B:3C:4D'],
            ['tipe_log' => 'RFID_TEMP_SCAN', 'ip_address' => '127.0.0.1']
        );

        AuditLog::firstOrCreate(
            ['deskripsi' => 'Presensi RFID Berhasil - Muhammad Rafli (NIM: 2241720001)'],
            ['tipe_log' => 'ACCESS_GRANTED', 'ip_address' => '192.168.1.101']
        );

        AuditLog::firstOrCreate(
            ['deskripsi' => 'Percobaan Tap Kartu Unregistered (UID: XX99YY88)'],
            ['tipe_log' => 'ACCESS_DENIED', 'ip_address' => '192.168.1.104']
        );
    }
}
