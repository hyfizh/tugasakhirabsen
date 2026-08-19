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

            $mahasiswas = Mahasiswa::where('kelas_id', $jadwal->kelas_id)->get();

            // Evaluasi per slot jam pelajaran (Jam 1, Jam 2, Jam 3, dsb)
            for ($j = $jamStart; $j <= $jamEnd; $j++) {
                $slotStartTime = $sessionStartTimes[$j] ?? '07:30';
                // Jam absen ditutup tepat 15 menit setelah jam mulai slot tersebut (misal Jam 1: 07:30 + 15m = 07:45 WIB)
                $slotCloseTime = date('H:i', strtotime($slotStartTime . ' + 15 minutes'));

                // Jika waktu saat ini sudah melewati batas tutup absen slot jam ke-j (misal > 07:45 WIB)
                if ($currentTime > $slotCloseTime) {
                    foreach ($mahasiswas as $mhs) {
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

                            // Otomatis cek & kirim email SP jika alpa mahasiswa mencapai threshold SP 1, 2, atau 3
                            \App\Http\Controllers\AdminController::checkAndSendAutoSpEmail($mhs);
                        }
                    }
                }
            }
        }

        $this->info("Selesai! Berhasil menambahkan $totalAlphaAdded catatan Alpa (A) untuk mahasiswa yang tidak absen.");
        return 0;
    }
}
