<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Mahasiswa;
use App\Models\Jadwal;
use App\Models\Absensi;

class CleanupTestSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:cleanup-absensi {rfid=890227517149}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bersihkan jadwal testing dan log absensi hasil pengujian malam.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rfid = $this->argument('rfid');
        $mahasiswa = Mahasiswa::where('rfid_uid', $rfid)->first();

        $todayStr = date('Y-m-d');
        $deletedAbsensi = 0;

        if ($mahasiswa) {
            $deletedAbsensi = Absensi::where('mahasiswa_id', $mahasiswa->id)
                ->where('tanggal', $todayStr)
                ->delete();
        }

        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $todayName = $days[date('w')];

        $deletedJadwal = 0;
        if ($mahasiswa) {
            $deletedJadwal = Jadwal::where('kelas_id', $mahasiswa->kelas_id)
                ->where('hari', $todayName)
                ->where('jam_mulai', 1)
                ->where('jam_selesai', 10)
                ->delete();
        }

        $this->info("=================================================");
        $this->info("🧹 PEMBERSIHAN DATA TESTING BERHASIL DILAKUKAN!");
        $this->info("=================================================");
        $this->line("• Record Absensi Testing Dihapus : {$deletedAbsensi}");
        $this->line("• Record Jadwal Testing Dihapus  : {$deletedJadwal}");
        $this->info("-------------------------------------------------");
        $this->comment("Database telah kembali bersih dan sesuai kondisi semula.");
        return 0;
    }
}
