<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Presensi Berhasil</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        .wrapper {
            width: 100%;
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #1e3a8a 0%, #0284c7 100%);
            color: #ffffff;
            padding: 30px 25px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 12px;
            opacity: 0.9;
        }
        .content {
            padding: 30px 25px;
        }
        .status-badge {
            display: inline-block;
            background-color: #dcfce7;
            color: #15803d;
            font-weight: bold;
            font-size: 12px;
            padding: 6px 16px;
            border-radius: 50px;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: #f8fafc;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #f1f5f9;
        }
        .info-table td {
            padding: 12px 16px;
            font-size: 13px;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-table tr:last-child td {
            border-bottom: none;
        }
        .info-label {
            color: #64748b;
            font-weight: 600;
            width: 35%;
        }
        .info-value {
            color: #0f172a;
            font-weight: 700;
        }
        .footer {
            background-color: #f1f5f9;
            padding: 20px 25px;
            text-align: center;
            font-size: 11px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>POLITEKNIK NEGERI PADANG</h1>
            <p>Sistem Presensi Akurat IoT &amp; Face Recognition (Absen TI)</p>
        </div>

        <div class="content">
            <div style="text-align: center;">
                <span class="status-badge">✅ PRESENSI TERATUR &amp; VALID</span>
                <h2 style="font-size: 18px; color: #0f172a; margin: 0 0 10px 0;">Halo, {{ $mahasiswa->nama_lengkap }}!</h2>
                <p style="font-size: 13px; color: #475569; margin: 0 0 20px 0;">
                    Presensi kehadiran Anda telah berhasil direkam oleh perangkat IoT Hardware di ruang perkuliahan.
                </p>
            </div>

            <table class="info-table">
                <tr>
                    <td class="info-label">NIM Mahasiswa</td>
                    <td class="info-value">{{ $mahasiswa->nim }}</td>
                </tr>
                <tr>
                    <td class="info-label">Kelas / Rombel</td>
                    <td class="info-value">{{ $mahasiswa->kelas->nama_kelas ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Mata Kuliah</td>
                    <td class="info-value">{{ $jadwal->mataKuliah->nama_mk ?? 'Sesi Perkuliahan' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Waktu Tap RFID</td>
                    <td class="info-value">{{ $absensi->waktu_tap_rfid ? \Carbon\Carbon::parse($absensi->waktu_tap_rfid)->locale('id')->isoFormat('D MMMM Y, HH:mm:ss [WIB]') : date('d-m-Y H:i:s') }}</td>
                </tr>
                <tr>
                    <td class="info-label">Status Kehadiran</td>
                    <td class="info-value" style="color: #16a34a;">
                        {{ $absensi->status === 'T' ? 'Terlambat (Valid Sesi)' : 'Hadir Tepat Waktu' }}
                    </td>
                </tr>
            </table>

            <p style="font-size: 12px; color: #64748b; margin-top: 25px; text-align: center;">
                <em>Jika Anda merasa tidak melakukan tapping kartu RFID pada waktu tersebut, silakan hubungi Dosen Pengampu atau Admin Jurusan.</em>
            </p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Jurusan Teknologi Informasi - Politeknik Negeri Padang.<br>
            Pesan otomatis ini dikirim oleh sistem EduAttend IoT. Mohon tidak membalas email ini.
        </div>
    </div>
</body>
</html>
