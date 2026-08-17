<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Presensi Mahasiswa - {{ $selectedKelas->nama_kelas ?? 'Politeknik Negeri Padang' }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1cm 1.5cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #111;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .header h2 {
            font-size: 13px;
            margin: 0;
            text-transform: uppercase;
        }
        .header h1 {
            font-size: 15px;
            margin: 2px 0;
            text-transform: uppercase;
        }
        .header p {
            font-size: 10px;
            margin: 2px 0;
            color: #444;
        }
        .title-box {
            border-top: 1px solid #000;
            margin-top: 6px;
            padding-top: 6px;
        }
        .title-box h3 {
            font-size: 12px;
            margin: 0;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .title-box p {
            font-size: 10px;
            margin: 2px 0 0 0;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #333;
            padding: 5px 8px;
            font-size: 10px;
        }
        th {
            background-color: #f1f5f9;
            text-transform: uppercase;
            font-weight: bold;
            text-align: center;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 4px;
        }
        .badge-h { background-color: #dcfce7; color: #166534; }
        .badge-t { background-color: #fef3c7; color: #92400e; }
        .badge-i { background-color: #e0f2fe; color: #075985; }
        .badge-s { background-color: #f3e8ff; color: #6b21a8; }
        .badge-a { background-color: #ffe4e6; color: #991b1b; }
        
        .footer {
            margin-top: 30px;
            width: 100%;
        }
        .footer-table {
            width: 100%;
            border: none;
        }
        .footer-table td {
            border: none;
            vertical-align: top;
            font-size: 10px;
        }
    </style>
</head>
<body>

    <!-- Kop Surat Resmi -->
    <div class="header">
        <h2>KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI</h2>
        <h1>POLITEKNIK NEGERI PADANG</h1>
        <p>Kampus Limau Manis, Pauh, Kota Padang, Sumatera Barat 25163 &bull; Telepon: (0751) 72590</p>
        <div class="title-box">
            <h3>{{ $rekapTab === 'mingguan' ? 'LAPORAN REKAPITULASI PRESENSI MINGGUAN (SENIN - SABTU)' : 'LAPORAN REKAPITULASI TOTAL PRESENSI MAHASISWA' }}</h3>
            <p>Kelas: {{ $selectedKelas->nama_kelas ?? 'TI-3A' }} &bull; Periode: {{ $monthsList[$bulan] ?? '' }} {{ $tahun }} &bull; Semester: Ganjil</p>
        </div>
    </div>

    @if ($rekapTab === 'mingguan')
        <!-- Tabel Rekap Mingguan (Senin s/d Sabtu) -->
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">NO</th>
                    <th style="width: 15%;">NIM</th>
                    <th style="width: 30%;">NAMA MAHASISWA</th>
                    @foreach ($daysOfWeek as $dayName => $dateVal)
                        <th style="width: 7%;">
                            {{ strtoupper($dayName) }}<br>
                            <span style="font-size: 8px; font-weight: normal;">{{ date('d/m', strtotime($dateVal)) }}</span>
                        </th>
                    @endforeach
                    <th style="width: 4%;">S</th>
                    <th style="width: 4%;">I</th>
                    <th style="width: 4%;">A</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $index => $mhs)
                    @php
                        $sakitW = $weeklyTotals[$mhs->id]['S'] ?? 0;
                        $izinW = $weeklyTotals[$mhs->id]['I'] ?? 0;
                        $alpaW = $weeklyTotals[$mhs->id]['A'] ?? 0;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ $mhs->nim }}</td>
                        <td class="font-bold">{{ $mhs->nama_lengkap }}</td>
                        @foreach ($daysOfWeek as $dayName => $dateVal)
                            @php
                                $status = null;
                                if (isset($weeklyAbsensi[$mhs->id][$dateVal])) {
                                    foreach ($weeklyAbsensi[$mhs->id][$dateVal] as $jam => $st) {
                                        $status = $st;
                                    }
                                }
                            @endphp
                            <td class="text-center">
                                @if ($status === 'H')
                                    <span class="badge badge-h">H</span>
                                @elseif ($status === 'T')
                                    <span class="badge badge-t">T</span>
                                @elseif ($status === 'I')
                                    <span class="badge badge-i">I</span>
                                @elseif ($status === 'S')
                                    <span class="badge badge-s">S</span>
                                @elseif ($status === 'A')
                                    <span class="badge badge-a">A</span>
                                @else
                                    -
                                @endif
                            </td>
                        @endforeach
                        <td class="text-center font-bold">{{ $sakitW }}</td>
                        <td class="text-center font-bold">{{ $izinW }}</td>
                        <td class="text-center font-bold" style="{{ $alpaW > 0 ? 'color: red;' : '' }}">{{ $alpaW }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center">Tidak ada data mahasiswa pada kelas ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @else
        <!-- Tabel Rekap Total Bulanan -->
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">NO</th>
                    <th style="width: 15%;">NIM</th>
                    <th style="width: 35%;">NAMA MAHASISWA</th>
                    <th style="width: 10%;">HADIR (H)</th>
                    <th style="width: 10%;">IZIN (I)</th>
                    <th style="width: 10%;">SAKIT (S)</th>
                    <th style="width: 10%;">ALPA (A)</th>
                    <th style="width: 15%;">KEHADIRAN (%)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $index => $mhs)
                    @php
                        $sakit = $monthlyTotals[$mhs->id]['S'] ?? 0;
                        $izin = $monthlyTotals[$mhs->id]['I'] ?? 0;
                        $alpa = $monthlyTotals[$mhs->id]['A'] ?? 0;
                        $hadir = 14 - ($sakit + $izin + $alpa);
                        if ($hadir < 0) $hadir = 0;
                        $percentage = round(($hadir / 14) * 100);
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ $mhs->nim }}</td>
                        <td class="font-bold">{{ $mhs->nama_lengkap }}</td>
                        <td class="text-center font-bold">{{ $hadir }}</td>
                        <td class="text-center">{{ $izin }}</td>
                        <td class="text-center">{{ $sakit }}</td>
                        <td class="text-center font-bold" style="{{ $alpa > 0 ? 'color: red;' : '' }}">{{ $alpa }}</td>
                        <td class="text-center font-bold" style="{{ $percentage < 75 ? 'color: red;' : 'color: green;' }}">
                            {{ $percentage }}%
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada data mahasiswa pada kelas ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <!-- Tanda Tangan Footer -->
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td style="width: 50%; text-align: center;">
                    Mengetahui,<br>
                    <strong>Ketua Jurusan Teknologi Informasi</strong>
                    <br><br><br><br>
                    <u><strong>Dr. Eng. Erwadi, M.T.</strong></u><br>
                    NIP. 197203151998021001
                </td>
                <td style="width: 50%; text-align: center;">
                    Padang, {{ date('d F Y') }}<br>
                    <strong>Dosen Pengampu / Admin</strong>
                    <br><br><br><br>
                    <u><strong>Admin EduAttend IoT</strong></u><br>
                    NIP/NIDN. System Generated
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
