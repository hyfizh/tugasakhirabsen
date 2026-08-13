<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Mahasiswa;
use App\Models\Jadwal;
use App\Models\MataKuliah;
use App\Models\Dosen;

class SetupTestSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:setup-absensi {rfid=890227517149}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Buat Jadwal Testing Absensi Malam untuk Mahasiswa RFID tertentu hari ini.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rfid = $this->argument('rfid');
        $mahasiswa = Mahasiswa::where('rfid_uid', $rfid)->first();

        if (!$mahasiswa) {
            $this->error("Mahasiswa dengan RFID UID '$rfid' tidak ditemukan di database!");
            return 1;
        }

        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $todayName = $days[date('w')];

        $matkul = MataKuliah::first();
        $dosen = Dosen::first();

        if (!$matkul || !$dosen) {
            $this->error("Mata Kuliah atau Dosen belum ada di database!");
            return 1;
        }

        $jadwal = Jadwal::updateOrCreate(
            [
                'kelas_id' => $mahasiswa->kelas_id,
                'hari'     => $todayName,
            ],
            [
                'mata_kuliah_id'          => $matkul->id,
                'dosen_id'               => $dosen->id,
                'jam_mulai'              => 1,
                'jam_selesai'            => 10,
                'toleransi_keterlambatan' => 15,
            ]
        );

        $this->info("=================================================");
        $this->info("✅ JADWAL TESTING ABSENSI MALAM BERHASIL DIBUAT!");
        $this->info("=================================================");
        $this->line("• Mahasiswa : {$mahasiswa->nama_lengkap} ({$mahasiswa->nim})");
        $this->line("• RFID UID  : {$mahasiswa->rfid_uid}");
        $this->line("• Kelas ID  : {$mahasiswa->kelas_id} ({$mahasiswa->kelas->nama_kelas})");
        $this->line("• Hari      : {$todayName}");
        $this->line("• Jam Sesi  : 1 s/d 10 (07:00 - 16:50 WIB / Aktif Sepanjang Hari)");
        $this->line("• MataKuliah: {$matkul->nama_mk}");
        $this->line("• Dosen     : {$dosen->nama_dosen}");
        $this->info("-------------------------------------------------");
        $this->comment("Anda sekarang dapat melakukan Tapping RFID & Verifikasi Wajah kapan saja!");
        return 0;
    }
}
