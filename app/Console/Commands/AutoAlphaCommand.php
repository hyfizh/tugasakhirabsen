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

        $sessionEndTimes = [
            1  => '08:20', 2  => '09:10', 3  => '10:00', 4  => '11:00', 5  => '11:50',
            6  => '12:40', 7  => '14:20', 8  => '15:10', 9  => '16:00', 10 => '16:50',
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
            $jamEndIndex = (int) ($jadwal->jam_selesai ?? $jadwal->jam_mulai ?? 4);
            $endTime = $sessionEndTimes[$jamEndIndex] ?? '11:00';

            // Jika waktu saat ini telah melewati batas jam_selesai matkul
            if ($currentTime > $endTime) {
                $mahasiswas = Mahasiswa::where('kelas_id', $jadwal->kelas_id)->get();
                $jamStart = (int) ($jadwal->jam_mulai ?? 1);
                $jamEnd   = (int) ($jadwal->jam_selesai ?? 4);
                if ($jamEnd < $jamStart) $jamEnd = $jamStart + 3;
                if ($jamEnd > 10) $jamEnd = 10;

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
