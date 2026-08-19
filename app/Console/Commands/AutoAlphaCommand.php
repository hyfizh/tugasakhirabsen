<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Jadwal;
use App\Models\Mahasiswa;
use App\Models\Absensi;
use Carbon\Carbon;

class AutoAlphaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'absensi:auto-alpha {--kelas= : ID Kelas tertentu}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis menandai status Alpa (A) bagi mahasiswa yang tidak absen hingga jam mata kuliah selesai.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now('Asia/Jakarta');
        $todayDate = $now->format('Y-m-d');
        $currentTime = $now->format('H:i');

        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $todayName = $days[date('w')];

        $sessionStartTimes = [
            1  => '07:30', 2  => '08:20', 3  => '09:10', 4  => '10:10', 5  => '11:00',
            6  => '11:50', 7  => '13:30', 8  => '14:20', 9  => '15:10', 10 => '16:00',
        ];

        $kelasId = $this->option('kelas');
        $jadwalQuery = Jadwal::where('hari', $todayName);
        if ($kelasId) {
            $jadwalQuery->where('kelas_id', $kelasId);
        }
        $jadwals = $jadwalQuery->get();

        $this->info("Menjalankan Auto-Alpha Check untuk tanggal $todayDate jam $currentTime WIB...");

        $totalAlphaAdded = 0;

        foreach ($jadwals as $jadwal) {
            $jamStart = (int) ($jadwal->jam_mulai ?? 1);
            $jamEnd   = (int) ($jadwal->jam_selesai ?? $jamStart);
            if ($jamEnd < $jamStart) $jamEnd = $jamStart + 2;
            if ($jamEnd > 10) $jamEnd = 10;

            $startTime = $sessionStartTimes[$jamStart] ?? '07:30';
            $toleranceMins = (int) ($jadwal->toleransi_keterlambatan ?? 30);
            if ($toleranceMins < 15) $toleranceMins = 30;

            // Batas waktu absen tutup (jam_mulai + toleransi keterlambatan)
            $windowCloseTime = date('H:i', strtotime($startTime . " + {$toleranceMins} minutes"));

            // Otomatis Alpa jika jam absen sudah ditutup (misal > 08:00 WIB untuk Jam ke-1)
            if ($currentTime > $windowCloseTime) {
                $mahasiswas = Mahasiswa::where('kelas_id', $jadwal->kelas_id)->get();

                foreach ($mahasiswas as $mhs) {
                    for ($j = $jamStart; $j <= $jamEnd; $j++) {
                        $exists = Absensi::where('mahasiswa_id', $mhs->id)
                            ->where('tanggal', $todayDate)
                            ->where('jam_pelajaran_ke', $j)
                            ->exists();

                        if (!$exists) {
                            Absensi::create([
                                'mahasiswa_id'     => $mhs->id,
                                'jadwal_id'        => $jadwal->id,
                                'tanggal'          => $todayDate,
                                'jam_pelajaran_ke' => $j,
                                'status'           => 'A',
                                'waktu_tap_rfid'   => null,
                            ]);
                            $totalAlphaAdded++;
                        }
                    }
                }
            }
        }

        $this->info("Selesai! Berhasil menambahkan $totalAlphaAdded catatan Alpa (A) untuk mahasiswa yang tidak absen.");
        return 0;
    }
}
