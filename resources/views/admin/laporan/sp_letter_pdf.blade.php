<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Peringatan - {{ $mahasiswa->nama_lengkap }}</title>
    <style>
        @page {
            margin: 1.5cm 2cm 2cm 2.5cm;
        }
        body {
            font-family: 'Times-Roman', 'Times New Roman', serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000000;
            margin: 0;
            padding: 0;
        }
        
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .kop-logo {
            width: 75px;
            vertical-align: middle;
            text-align: center;
        }
        .kop-logo img {
            width: 70px;
            height: 70px;
        }
        .kop-text {
            text-align: center;
            vertical-align: middle;
            padding: 0 5px;
        }
        .kop-instansi {
            font-size: 11pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }
        .kop-kampus {
            font-size: 13pt;
            font-weight: bold;
            margin: 1px 0;
            text-transform: uppercase;
        }
        .kop-jurusan {
            font-size: 14pt;
            font-weight: bold;
            margin: 1px 0;
            text-transform: uppercase;
        }
        .kop-alamat {
            font-size: 8.5pt;
            margin: 2px 0 0 0;
            line-height: 1.2;
        }
        .garis-kop {
            border-top: 2.5px solid #000000;
            border-bottom: 1px solid #000000;
            height: 2px;
            margin-top: 4px;
            margin-bottom: 12px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 10.5pt;
        }
        .meta-table td {
            vertical-align: top;
            padding: 1.5px 0;
        }

        .content-body {
            font-size: 11pt;
            text-align: justify;
            line-height: 1.4;
        }
        .content-body p {
            margin: 0 0 8px 0;
            text-indent: 32px;
        }

        .table-identitas {
            width: 90%;
            margin: 8px auto 10px auto;
            border-collapse: collapse;
            font-size: 10.5pt;
        }
        .table-identitas td {
            padding: 2.5px 4px;
            vertical-align: top;
        }

        .table-peringatan {
            width: 96%;
            margin: 10px auto 12px auto;
            border-collapse: collapse;
            font-size: 10pt;
            text-align: center;
        }
        .table-peringatan th {
            background-color: #f1f5f9;
            border: 1.5px solid #000000;
            padding: 6px 8px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9.5pt;
        }
        .table-peringatan td {
            border: 1.5px solid #000000;
            padding: 7px 8px;
            font-weight: bold;
        }

        .table-ttd {
            width: 100%;
            margin-top: 16px;
            border-collapse: collapse;
            font-size: 10.5pt;
        }
        .col-left {
            width: 30%;
            text-align: center;
            vertical-align: top;
        }
        .col-mid {
            width: 30%;
            text-align: center;
            vertical-align: top;
        }
        .col-right {
            width: 40%;
            text-align: center;
            vertical-align: top;
        }
        .ttd-ruang {
            height: 46px;
        }
        .ttd-nama {
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body>

    @php
        $cleanNamaMahasiswa = preg_replace('/\s*\(SP\s*\d+\)/i', '', $mahasiswa->nama_lengkap);

        // Prepare image paths for DomPDF
        $logoLeftPath = null;
        if (file_exists(public_path('images/logo.jpg'))) {
            $logoLeftPath = public_path('images/logo.jpg');
        } elseif (file_exists(public_path('images/logo.png'))) {
            $logoLeftPath = public_path('images/logo.png');
        } elseif (file_exists(public_path('images/logo_pnp.png'))) {
            $logoLeftPath = public_path('images/logo_pnp.png');
        }

        $logoRightPath = null;
        if (file_exists(public_path('images/logo2.png'))) {
            $logoRightPath = public_path('images/logo2.png');
        } elseif (file_exists(public_path('images/logo2.jpg'))) {
            $logoRightPath = public_path('images/logo2.jpg');
        }
    @endphp

    <!-- KOP SURAT TABLE FOR DOMPDF -->
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                @if ($logoLeftPath)
                    <img src="{{ $logoLeftPath }}" alt="Logo Kiri">
                @else
                    <div style="font-weight: bold; font-size: 14pt; color: #1e3a8a;">PNP</div>
                @endif
            </td>
            <td class="kop-text">
                <div class="kop-instansi">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</div>
                <div class="kop-kampus">POLITEKNIK NEGERI PADANG</div>
                <div class="kop-jurusan">JURUSAN TEKNOLOGI INFORMASI</div>
                <div class="kop-alamat">
                    Kampus Politeknik Negeri Padang, Limau Manis, Kec. Pauh, Kota Padang 25164<br>
                    Telepon: (0751) 72590 | Fax: (0751) 72576 | Laman: https://ti.pnp.ac.id | Surel: info@pnp.ac.id
                </div>
            </td>
            <td class="kop-logo">
                @if ($logoRightPath)
                    <img src="{{ $logoRightPath }}" alt="Logo Kanan">
                @else
                    <div style="font-weight: bold; font-size: 14pt; color: #0f172a;">TI</div>
                @endif
            </td>
        </tr>
    </table>

    <div class="garis-kop"></div>

    <!-- METADATA SURAT -->
    <table class="meta-table">
        <tr>
            <td style="width: 12%;">Nomor</td>
            <td style="width: 2%;">:</td>
            <td style="width: 46%;">B/{{ rand(100, 299) }}/PL9.3.1/KM/{{ date('Y') }}</td>
            <td style="width: 40%; text-align: right;">
                Padang, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}
            </td>
        </tr>
        <tr>
            <td>Lampiran</td>
            <td>:</td>
            <td>- (Satu Lembar)</td>
            <td></td>
        </tr>
        <tr>
            <td>Hal</td>
            <td>:</td>
            <td><strong>{{ $spTitle }}</strong></td>
            <td></td>
        </tr>
    </table>

    <!-- ALAMAT TUJUAN SURAT -->
    <div style="margin-bottom: 14px; font-size: 11pt; line-height: 1.35;">
        Kepada Yth.<br>
        Orang Tua / Wali dari Saudara/i:<br>
        <strong>{{ $cleanNamaMahasiswa }}</strong><br>
        di Tempat
    </div>

    <!-- ISI SURAT -->
    <div class="content-body">
        <p>
            Dengan hormat, berdasarkan rekapitulasi kehadiran mahasiswa semester berjalan, Pimpinan Jurusan Teknologi Informasi memanggil dan menerbitkan Surat Peringatan kepada:
        </p>

        <table class="table-identitas">
            <tr>
                <td style="width: 34%;">Nama Lengkap</td>
                <td style="width: 4%; text-align: center;">:</td>
                <td style="width: 62%; font-weight: bold;">{{ $cleanNamaMahasiswa }}</td>
            </tr>
            <tr>
                <td>NIM / Kelas</td>
                <td style="text-align: center;">:</td>
                <td style="font-weight: bold;">{{ $mahasiswa->nim }} — {{ $mahasiswa->kelas->nama_kelas ?? '-' }} (Teknologi Informasi)</td>
            </tr>
            <tr>
                <td>Kontak / No. HP</td>
                <td style="text-align: center;">:</td>
                <td style="font-weight: bold;">{{ $mahasiswa->no_hp ?? '-' }}</td>
            </tr>
        </table>

        <p>
            Mahasiswa bersangkutan tercatat memiliki ketidakhadiran tanpa keterangan (Alpa) yang melampaui batas toleransi akademis, dengan rincian sebagai berikut:
        </p>

        <table class="table-peringatan">
            <thead>
                <tr>
                    <th style="width: 30%;">Total Jam Alpa</th>
                    <th style="width: 40%; background-color: #f1f5f9;">Kategori Peringatan</th>
                    <th style="width: 30%;">Kewajiban Denda Kompensasi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="font-size: 12.5pt; color: #b91c1c;">{{ $totalAlpaHours }} Jam Pelajaran</td>
                    <td style="color: #b91c1c; font-weight: bold;">{{ $spTitle }}</td>
                    <td style="font-size: 11.5pt;">{{ $compensationPenalty }} Jam Kerja Kompensasi</td>
                </tr>
            </tbody>
        </table>

        <p>
            Mohon perhatian Orang Tua / Wali. Mahasiswa diwajibkan menyelesaikan denda kompensasi sebelum Ujian Akhir Semester (UAS). Apabila Alpa mencapai <strong>50 Jam Pelajaran</strong>, mahasiswa bersangkutan dikenakan sanksi <strong>Drop Out (DO)</strong> sesuai Peraturan Akademik PNP.
        </p>
        <p>
            Demikian Surat Peringatan ini disampaikan untuk diperhatikan.
        </p>
    </div>

    <!-- TANDA TANGAN OFFICIAL (KETUA JURUSAN) -->
    <table class="table-ttd">
        <tr>
            <td style="width: 55%;"></td>
            <td style="width: 45%; text-align: center; vertical-align: top;">
                Padang, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}<br>
                <strong>Ketua Jurusan Teknologi Informasi</strong>
                <div class="ttd-ruang"></div>
                <div class="ttd-nama">Rika Idmayanti, S.T., M.Kom.</div>
                <div style="font-size: 9.5pt;">NIP. 198007202005012002</div>
            </td>
        </tr>
    </table>

</body>
</html>
