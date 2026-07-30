<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Kelas;
use App\Models\Dosen;
use App\Models\MataKuliah;
use App\Models\Jadwal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:import {url=https://presensi.pnp.ac.id/ti/20252/TIGenap2025-2026v1.4_1_subgroups_days_horizontal.html}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import class schedules dynamically from the PNP TI html schedule matrix';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = $this->argument('url');
        $this->info("Fetching schedule from: $url");

        try {
            $response = \Illuminate\Support\Facades\Http::withoutVerifying()->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
            ])->timeout(30)->get($url);
            
            if (!$response->successful()) {
                $this->error("HTTP request returned status: " . $response->status());
                return 1;
            }
            $html = $response->body();
        } catch (\Exception $e) {
            $this->error("HTTP request failed: " . $e->getMessage());
            return 1;
        }

        $this->info("Parsing HTML content...");

        // Disable standard libxml errors for parsing raw HTML
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        // Find all tables that have captions (these represent classes)
        $tables = $xpath->query('//table[caption]');

        if ($tables->length === 0) {
            $this->error("No schedule tables found in the HTML.");
            return 1;
        }

        $this->info("Found " . $tables->length . " schedule tables. Importing...");

        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        DB::transaction(function () use ($tables, $days, $xpath) {
            foreach ($tables as $table) {
                // Get class name from caption
                $caption = $table->getElementsByTagName('caption')->item(0);
                if (!$caption) continue;

                $nameSpan = $xpath->query('.//span[@class="name"]', $caption)->item(0);
                if ($nameSpan) {
                    $className = trim($nameSpan->textContent);
                } else {
                    $className = trim($caption->textContent);
                }
                $className = str_replace(' Grup Otomat', '', $className);

                // Format class name dynamically (e.g. 3TK-2B-Regular -> Teknik Komputer 2B)
                $tempClassName = str_ireplace(['-regular', '-reguler', '-mandiri'], '', $className);
                if (preg_match('/^(?:[34])?(TK|MI|TRPL|TI|RPL|ANM)-?([0-9][A-Za-z])$/i', $tempClassName, $matches)) {
                    $prodiCode = strtoupper($matches[1]);
                    $classGroup = strtoupper($matches[2]);
                    
                    $prodiMap = [
                        'TK' => 'Teknik Komputer',
                        'MI' => 'Manajemen Informatika',
                        'TRPL' => 'Teknologi Rekayasa Perangkat Lunak',
                        'RPL' => 'Teknologi Rekayasa Perangkat Lunak',
                        'TI' => 'Teknologi Informasi',
                        'ANM' => 'Animasi'
                    ];
                    
                    $prodiName = $prodiMap[$prodiCode] ?? $prodiCode;
                    $className = $prodiName . ' ' . $classGroup;
                }

                $this->comment("Importing schedule for Class: $className");

                // Find or create Kelas
                $kelas = Kelas::query()->firstOrCreate(['nama_kelas' => $className]);

                $rows = $table->getElementsByTagName('tbody')->item(0)->getElementsByTagName('tr');
                $rowCount = $rows->length;
                $colCount = 8; // 1 (time header) + 7 (days Senin-Minggu)

                // Grid to keep track of occupied cells from rowspan
                $grid = [];
                for ($r = 0; $r < $rowCount; $r++) {
                    $grid[$r] = array_fill(1, 7, null);
                }
                
                for ($r = 0; $r < $rows->length; $r++) {
                    $row = $rows->item($r);
                    if ($row->getAttribute('class') === 'foot') continue; // skip footer info row

                    $cols = $row->getElementsByTagName('td');
                    $th = $row->getElementsByTagName('th')->item(0);
                    if (!$th) continue; // time slot header

                    $timeSlot = trim($th->textContent);

                    $tdIndex = 0;
                    for ($c = 1; $c <= 7; $c++) {
                        // If cell is already covered by a previous rowspan, skip it
                        if ($grid[$r][$c] !== null) {
                            continue;
                        }

                        $td = $cols->item($tdIndex);
                        if (!$td) {
                            continue;
                        }
                        $tdIndex++;

                        $rowspan = $td->hasAttribute('rowspan') ? (int) $td->getAttribute('rowspan') : 1;
                        $cellHtml = '';
                        foreach ($td->childNodes as $child) {
                            $cellHtml .= $td->ownerDocument->saveHTML($child);
                        }

                        // Replace br tags with newlines and clean up
                        $cellTextClean = str_replace(['<br>', '<br />', '<br/>'], "\n", $cellHtml);
                        $lines = array_filter(array_map('trim', explode("\n", strip_tags($cellTextClean))));

                        // Mark grid slots occupied by rowspan
                        for ($i = 0; $i < $rowspan; $i++) {
                            if (($r + $i) < $rowCount) {
                                $grid[$r + $i][$c] = $lines ?: 'empty';
                            }
                        }

                        // If empty, skip database import
                        if (empty($lines) || trim($td->textContent) === '---') {
                            continue;
                        }

                        // Structure of schedule cell contents:
                        // Line 1: Class (e.g. 3MI-1A-REGULER)
                        // Line 2: Subject Code and Name (e.g. ISY3303 Pemrograman Berorientasi Objek Praktek PRAKTEK)
                        // Line 3: Lecturers (e.g. Tri Lestari, Rahmi Putri Kurnia)
                        // Line 4: Room (e.g. E301-LABOR Pemrograman 1)
                        if (count($lines) < 2) continue;

                        $subjectLine = $lines[1] ?? '';
                        $lecturerLine = $lines[2] ?? '';

                        // Parse subject line (Code is the first word, Name is the rest)
                        $subjectWords = explode(' ', $subjectLine);
                        $subjectCode = $subjectWords[0] ?? 'MK' . rand(100, 999);
                        $subjectName = implode(' ', array_slice($subjectWords, 1));
                        if (empty($subjectName)) {
                            $subjectName = $subjectLine;
                        }

                        // Parse lecturer line (take first lecturer name if multiple)
                        $lecturers = explode(',', $lecturerLine);
                        $lecturerName = trim($lecturers[0] ?? 'Dosen Pengampu');

                        // Find or Create Lecturer Dosen
                        $dosenUsername = Str::slug($lecturerName, '');
                        if (empty($dosenUsername)) $dosenUsername = 'dosen' . rand(100, 999);

                        $dosenUser = User::query()->firstOrCreate(
                            ['username' => $dosenUsername],
                            [
                                'password' => Hash::make('password'),
                                'role' => 'dosen',
                                'is_password_changed' => true
                            ]
                        );

                        $dosen = Dosen::query()->firstOrCreate(
                            ['user_id' => $dosenUser->id],
                            [
                                'nip' => 'NIP' . rand(100000, 999999),
                                'nama_dosen' => $lecturerName,
                            ]
                        );

                        // Find or Create Subject MataKuliah
                        $mataKuliah = MataKuliah::query()->firstOrCreate(
                            ['kode_mk' => $subjectCode],
                            [
                                'nama_mk' => $subjectName,
                                'sks' => 3, // default SKS
                            ]
                        );

                        // Define lesson hours indices (1-indexed based on row index)
                        $jamMulai = $r + 1;
                        $jamSelesai = $r + $rowspan;
                        $dayName = $days[$c - 1]; // Senin-Minggu

                        // Insert Jadwal
                        Jadwal::query()->create([
                            'kelas_id' => $kelas->id,
                            'mata_kuliah_id' => $mataKuliah->id,
                            'dosen_id' => $dosen->id,
                            'hari' => $dayName,
                            'jam_mulai' => $jamMulai,
                            'jam_selesai' => $jamSelesai,
                            'toleransi_keterlambatan' => 15,
                        ]);
                    }
                }
            }
        });

        $this->info("Import completed successfully!");
        return 0;
    }
}
