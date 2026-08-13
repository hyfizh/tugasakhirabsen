<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Peringatan - {{ $mahasiswa->nama_lengkap }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.5cm 2cm 2cm 2.5cm;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            line-height: 1.45;
            color: #000000;
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>

    @php
        $cleanNamaMahasiswa = preg_replace('/\s*\(SP\s*\d+\)/i', '', $mahasiswa->nama_lengkap);

        $spRoman = $spRoman ?? ($spLevel == 1 ? 'I' : ($spLevel == 2 ? 'II' : 'III'));

        $fullKelas = $mahasiswa->kelas->nama_kelas ?? '';
        
        if (preg_match('/^(.*?)\s*([0-9]+[A-Za-z]+)$/', $fullKelas, $matches)) {
            $prodiRaw = trim($matches[1]);
            $kelasRaw = trim($matches[2]);
            $prodiDisplay = (str_contains(strtolower($prodiRaw), 'd-3') || str_contains(strtolower($prodiRaw), 'd3') || str_contains(strtolower($prodiRaw), 'd-4') || str_contains(strtolower($prodiRaw), 'd4')) 
                            ? $prodiRaw 
                            : 'D-3 ' . $prodiRaw;
            $prodiKelasText = $prodiDisplay . ', Kelas ' . $kelasRaw;
            $prodiOnlyText = $prodiDisplay;
        } else {
            $prodiKelasText = $fullKelas ? 'Prodi ' . $fullKelas : 'Teknologi Informasi';
            $prodiOnlyText = $fullKelas ?: 'Teknologi Informasi';
        }

        $logoPath = null;
        if (file_exists(public_path('img/logo-pnp.png'))) {
            $logoPath = public_path('img/logo-pnp.png');
        } elseif (file_exists(public_path('images/logo.png'))) {
            $logoPath = public_path('images/logo.png');
        } elseif (file_exists(public_path('images/logo_pnp.png'))) {
            $logoPath = public_path('images/logo_pnp.png');
        } elseif (file_exists(public_path('images/logo.jpg'))) {
            $logoPath = public_path('images/logo.jpg');
        }
    @endphp

    <!-- 1. KOP SURAT 2 KOLOM (15% LOGO, 85% HEADER TEKS) -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 2px;">
        <tr>
            <td style="width: 15%; text-align: center; vertical-align: middle;">
                @if ($logoPath)
                    <img src="{{ $logoPath }}" alt="Logo PNP" style="width: 75px; height: 75px; object-fit: contain;">
                @endif
            </td>
            <td style="width: 85%; text-align: center; vertical-align: middle; padding-left: 5px;">
                <div style="font-size: 10pt; font-weight: bold; margin: 0; text-transform: uppercase;">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</div>
                <div style="font-size: 12pt; font-weight: bold; margin: 1px 0; text-transform: uppercase;">POLITEKNIK NEGERI PADANG</div>
                <div style="font-size: 13pt; font-weight: bold; margin: 1px 0; text-transform: uppercase;">JURUSAN TEKNOLOGI INFORMASI</div>
                <div style="font-size: 8.5pt; margin: 3px 0 0 0; line-height: 1.25;">
                    Kampus Politeknik Negeri Padang, Limau Manis, Padang, Sumatera Barat<br>
                    Telepon (0751) 72590, Faks. (0751) 72576<br>
                    Laman: https://ti.pnp.ac.id | Surel: jurusan.ti@pnp.ac.id
                </div>
            </td>
        </tr>
    </table>

    <div style="border-top: 2.5px solid #000000; border-bottom: 1px solid #000000; height: 2px; margin-top: 4px; margin-bottom: 16px;"></div>

    <!-- 2. META SURAT (NOMOR, HAL, TANGGAL) -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 11pt;">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 18%;">Nomor</td>
                        <td style="width: 4%;">:</td>
                        <td style="width: 78%;">{{ $nomorSurat ?? ($nomor_surat ?? '414/PL9.8/EP/' . date('Y')) }}</td>
                    </tr>
                    <tr>
                        <td>Hal</td>
                        <td>:</td>
                        <td>Peringatan {{ $spRoman }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 40%; vertical-align: top; text-align: right;">
                {{ $tanggalSurat ?? ($tanggal_surat ?? \Carbon\Carbon::now()->locale('id')->isoFormat('DD MMMM Y')) }}
            </td>
        </tr>
    </table>

    <!-- 3. ALAMAT TUJUAN PENERIMA -->
    <div style="margin-bottom: 16px; font-size: 11pt; line-height: 1.4;">
        Yth. Sdr. <strong>{{ $cleanNamaMahasiswa }}</strong> BP {{ $mahasiswa->nim }}<br>
        Mahasiswa {{ $prodiKelasText }}<br>
        Politeknik Negeri Padang<br>
        di<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Limau Manis
    </div>

    <!-- 4. ISI PARAGRAF NASKAH SURAT RESMI -->
    <div style="font-size: 11pt; text-align: justify; line-height: 1.5;">
        <p style="margin: 0 0 12px 0; text-indent: 32px;">
            Sesuai dengan Peraturan Akademik Politeknik Negeri Padang nomor: 4597/PL.9/DL/2018 Pasal 26, tentang Ketidakhadiran, maka berdasarkan data kehadiran saudara yang ada di jurusan Teknologi Informasi, Prodi {{ $prodiOnlyText }} terhitung sampai minggu ke {{ $mingguKe ?? ($minggu_ke ?? 16) }} perkuliahan (sampai dengan tanggal {{ $tanggalAkhirHitung ?? ($tanggal_akhir_hitung ?? \Carbon\Carbon::now()->locale('id')->isoFormat('DD MMMM Y')) }}), absen Saudara pada semester {{ $semesterTipe ?? ($semester_tipe ?? 'Genap') }} tahun akademik {{ $tahunAkademik ?? ($tahun_akademik ?? '2025-2026') }} berjumlah {{ $totalAlpaHours }} Jam.
        </p>

        <p style="margin: 0 0 12px 0; text-indent: 32px;">
            Sehubungan dengan hal tersebut diatas, maka Saudara diberikan Peringatan {{ $spRoman }}, apabila Saudara tidak memperhatikan kehadiran selanjutnya akan diteruskan pada peringatan berikutnya.
        </p>

        <p style="margin: 0 0 12px 0; text-indent: 32px;">
            Demikianlah hal ini disampaikan untuk dapat diperhatikan.
        </p>
    </div>

    <!-- 5. BLOK TANDA TANGAN SEKRETARIS JURUSAN (CENTER-ALIGNED INSIDE RIGHT 45% BOX) -->
    <table style="width: 45%; float: right; margin-top: 36px; border-collapse: collapse; font-size: 11pt;">
        <tr>
            <td style="text-align: center; vertical-align: top;">
                a.n. Ketua Jurusan,<br>
                Sekretaris Jurusan
                <div style="height: 60px;"></div>
                <div style="font-weight: bold; text-decoration: underline;">{{ $pejabatNama ?? 'Humaira, ST., MT' }}</div>
                <div style="font-size: 10pt; margin-top: 2px;">NIP. {{ $pejabatNip ?? '19810319 200604 2 002' }}</div>
            </td>
        </tr>
    </table>
    <div style="clear: both;"></div>

</body>
</html>
