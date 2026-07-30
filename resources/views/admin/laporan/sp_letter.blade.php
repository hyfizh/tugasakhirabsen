<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Peringatan Resmi - {{ preg_replace('/\s*\(SP\s*\d+\)/i', '', $mahasiswa->nama_lengkap) }} (NIM: {{ $mahasiswa->nim }})</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* MARGIN OFFICIAL SURAT KEDINASAN POLITEKNIK NEGERI PADANG */
        @page {
            size: A4 portrait;
            margin: 1.5cm 2cm 2cm 2.5cm;
        }
        
        * {
            box-sizing: border-box;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            color: #000000;
            background-color: #f8fafc;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            font-size: 11pt;
            -webkit-print-color-adjust: exact;
        }

        /* Top Action Bar for Web Preview */
        .no-print-bar {
            background-color: #0f172a;
            color: #ffffff;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-family: 'Plus Jakarta Sans', sans-serif;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .bar-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 700;
        }

        .bar-badge {
            background: #ef4444;
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
        }

        .btn-group {
            display: flex;
            gap: 10px;
        }

        .btn-action {
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .btn-print {
            background-color: #4f46e5;
            color: white;
        }

        .btn-print:hover {
            background-color: #4338ca;
        }

        .btn-back {
            background-color: #475569;
            color: white;
        }

        .btn-back:hover {
            background-color: #334155;
        }

        /* Letter Sheet Container - Official ratio */
        .sheet-container {
            width: 210mm;
            max-width: 210mm;
            margin: 15px auto;
            background: #ffffff;
            padding: 1.5cm 2cm 2cm 2.5cm;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border-radius: 4px;
            position: relative;
        }

        /* Official KOP SURAT DUA LOGO (KIRI & KANAN) */
        .kop-header {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }

        .kop-logo-left {
            display: table-cell;
            width: 75px;
            vertical-align: middle;
            text-align: left;
        }

        .kop-text-cell {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            padding: 0 6px;
        }

        .kop-logo-right {
            display: table-cell;
            width: 75px;
            vertical-align: middle;
            text-align: right;
        }

        .kop-instansi {
            font-size: 11pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kop-kampus {
            font-size: 13pt;
            font-weight: bold;
            margin: 1px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kop-jurusan {
            font-size: 14pt;
            font-weight: bold;
            margin: 1px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kop-alamat {
            font-size: 8.5pt;
            margin: 2px 0 0 0;
            line-height: 1.2;
            font-style: normal;
        }

        /* Double Line Kop Surat */
        .garis-kop {
            border-top: 2.5px solid #000000;
            border-bottom: 1px solid #000000;
            height: 2px;
            margin-top: 4px;
            margin-bottom: 12px;
        }

        /* Official Letter Metadata Header */
        .surat-meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 10.5pt;
        }

        .surat-meta td {
            vertical-align: top;
            padding: 1.5px 0;
        }

        /* Paragraphs Body */
        .content-body {
            font-size: 11pt;
            text-align: justify;
            line-height: 1.4;
        }

        .content-body p {
            margin: 0 0 8px 0;
            text-indent: 32px;
        }

        /* Student Identity Box Table */
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

        .td-label {
            width: 34%;
            font-weight: normal;
        }

        .td-colon {
            width: 4%;
            text-align: center;
        }

        .td-value {
            width: 62%;
            font-weight: bold;
        }

        /* Warning Table Penalty Matrix */
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

        .highlight-sp {
            color: #b91c1c;
            font-weight: bold;
        }

        /* Official Signature 3 Columns Grid (Wide right column for full title) */
        .table-ttd {
            width: 100%;
            margin-top: 16px;
            border-collapse: collapse;
            page-break-inside: avoid;
            font-size: 10.5pt;
        }

        .col-ttd-left {
            width: 29%;
            text-align: center;
            vertical-align: top;
            padding: 0 2px;
        }

        .col-ttd-mid {
            width: 29%;
            text-align: center;
            vertical-align: top;
            padding: 0 2px;
        }

        .col-ttd-right {
            width: 42%;
            text-align: center;
            vertical-align: top;
            padding: 0 2px;
        }

        .ttd-ruang {
            height: 46px;
        }

        .ttd-nama {
            font-weight: bold;
            text-decoration: underline;
            white-space: nowrap;
        }

        .ttd-nip {
            font-size: 9.5pt;
            margin-top: 1px;
            white-space: nowrap;
        }

        .dot-line {
            display: inline-block;
            width: 120px;
            border-bottom: 1px dotted #000000;
            height: 12px;
            vertical-align: bottom;
        }

        /* Strict 1-Page A4 Print Media Styles */
        @media print {
            html, body {
                width: 210mm;
                height: 297mm;
                margin: 0 !important;
                padding: 0 !important;
                background-color: #ffffff !important;
            }

            @page {
                size: A4 portrait;
                margin: 1.5cm 2cm 2cm 2.5cm;
            }

            .no-print-bar {
                display: none !important;
            }

            .sheet-container {
                width: 100% !important;
                max-width: 100% !important;
                min-height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                page-break-after: avoid !important;
                page-break-before: avoid !important;
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>
<body>

    <!-- Web Navigation Action Bar (Hidden on Print) -->
    <div class="no-print-bar">
        <div class="bar-title">
            <i class="fa-solid fa-file-pdf text-indigo-400"></i>
            <span>Pratinjau Surat Peringatan Resmi (SP)</span>
            <span class="bar-badge">{{ $spTitle }}</span>
        </div>
        <div class="btn-group">
            <button onclick="window.history.back()" class="btn-action btn-back">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </button>
            <button onclick="window.print()" class="btn-action btn-print">
                <i class="fa-solid fa-print"></i> Cetak Browser / Printer
            </button>
            <a href="{{ route('admin.laporan.download-sp-pdf', $mahasiswa->id) }}" target="_blank" class="btn-action" style="background-color: #059669; color: white;">
                <i class="fa-solid fa-file-pdf"></i> Download PDF (DomPDF)
            </a>
        </div>
    </div>

    <!-- Official A4 Document Sheet -->
    <div class="sheet-container">

        <!-- KOP SURAT RESMI DUA LOGO (KIRI & KANAN) -->
        <div class="kop-header">
            <!-- LOGO KIRI (logo.jpg / logo.png) -->
            <div class="kop-logo-left">
                @if (file_exists(public_path('images/logo.jpg')))
                    <img src="{{ asset('images/logo.jpg') }}" alt="Logo Kiri" style="width: 75px; height: 75px; object-fit: contain;">
                @elseif (file_exists(public_path('images/logo.png')))
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Kiri" style="width: 75px; height: 75px; object-fit: contain;">
                @elseif (file_exists(public_path('images/logo_pnp.png')))
                    <img src="{{ asset('images/logo_pnp.png') }}" alt="Logo Kiri" style="width: 75px; height: 75px; object-fit: contain;">
                @elseif (file_exists(public_path('logo.png')))
                    <img src="{{ asset('logo.png') }}" alt="Logo Kiri" style="width: 75px; height: 75px; object-fit: contain;">
                @else
                    <!-- SVG Emblem Logo Kiri Default -->
                    <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" style="width: 72px; height: 72px;">
                        <circle cx="50" cy="50" r="46" fill="#1e3a8a" stroke="#f59e0b" stroke-width="4"/>
                        <path d="M50 12 L82 32 L82 68 L50 88 L18 68 L18 32 Z" fill="#0284c7" stroke="#ffffff" stroke-width="2"/>
                        <circle cx="50" cy="50" r="24" fill="#f59e0b"/>
                        <path d="M50 30 L55 42 L68 44 L58 53 L61 66 L50 59 L39 66 L42 53 L32 44 L45 42 Z" fill="#ffffff"/>
                        <text x="50" y="82" font-family="Arial, sans-serif" font-size="8" font-weight="bold" fill="#ffffff" text-anchor="middle">PNP</text>
                    </svg>
                @endif
            </div>

            <!-- TEKS HEADER KEMENTERIAN & INSTITUSI -->
            <div class="kop-text-cell">
                <h4 class="kop-instansi">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</h4>
                <h3 class="kop-kampus">POLITEKNIK NEGERI PADANG</h3>
                <h2 class="kop-jurusan">JURUSAN TEKNOLOGI INFORMASI</h2>
                <p class="kop-alamat">
                    Kampus Politeknik Negeri Padang, Limau Manis, Kec. Pauh, Kota Padang 25164<br>
                    Telepon: (0751) 72590 | Fax: (0751) 72576 | Laman: https://ti.pnp.ac.id | Surel: info@pnp.ac.id
                </p>
            </div>

            <!-- LOGO KANAN (logo2.png / logo2.jpg) -->
            <div class="kop-logo-right">
                @if (file_exists(public_path('images/logo2.png')))
                    <img src="{{ asset('images/logo2.png') }}" alt="Logo Kanan" style="width: 75px; height: 75px; object-fit: contain;">
                @elseif (file_exists(public_path('images/logo2.jpg')))
                    <img src="{{ asset('images/logo2.jpg') }}" alt="Logo Kanan" style="width: 75px; height: 75px; object-fit: contain;">
                @elseif (file_exists(public_path('logo2.png')))
                    <img src="{{ asset('logo2.png') }}" alt="Logo Kanan" style="width: 75px; height: 75px; object-fit: contain;">
                @else
                    <!-- SVG Emblem Logo Kanan Default (Teknologi Informasi) -->
                    <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" style="width: 72px; height: 72px;">
                        <rect x="10" y="10" width="80" height="80" rx="16" fill="#0f172a" stroke="#38bdf8" stroke-width="4"/>
                        <path d="M30 35 L50 20 L70 35 L70 65 L50 80 L30 65 Z" fill="#3b82f6" opacity="0.8"/>
                        <circle cx="50" cy="50" r="14" fill="#38bdf8"/>
                        <text x="50" y="54" font-family="Arial, sans-serif" font-size="10" font-weight="bold" fill="#0f172a" text-anchor="middle">TI</text>
                    </svg>
                @endif
            </div>
        </div>

        <!-- Garis Ganda Kop Surat -->
        <div class="garis-kop"></div>

        <!-- Metadata Header Surat Resmi -->
        <table class="surat-meta">
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

        @php
            $cleanNamaMahasiswa = preg_replace('/\s*\(SP\s*\d+\)/i', '', $mahasiswa->nama_lengkap);
        @endphp

        <!-- Alamat Tujuan Surat -->
        <div style="margin-bottom: 14px; font-size: 11pt; line-height: 1.35;">
            Kepada Yth.<br>
            Orang Tua / Wali dari Saudara/i:<br>
            <strong>{{ $cleanNamaMahasiswa }}</strong><br>
            di Tempat
        </div>

        <!-- Isi Dokumen Surat Ringkas & Padat -->
        <div class="content-body">
            <p>
                Dengan hormat, berdasarkan rekapitulasi kehadiran mahasiswa semester berjalan, Pimpinan Jurusan Teknologi Informasi memanggil dan menerbitkan Surat Peringatan kepada:
            </p>

            <!-- Tabel Identitas Mahasiswa -->
            <table class="table-identitas">
                <tr>
                    <td class="td-label">Nama Lengkap</td>
                    <td class="td-colon">:</td>
                    <td class="td-value">{{ $cleanNamaMahasiswa }}</td>
                </tr>
                <tr>
                    <td class="td-label">NIM / Kelas</td>
                    <td class="td-colon">:</td>
                    <td class="td-value">{{ $mahasiswa->nim }} — {{ $mahasiswa->kelas->nama_kelas ?? '-' }} (Teknologi Informasi)</td>
                </tr>
                <tr>
                    <td class="td-label">Kontak / No. HP</td>
                    <td class="td-colon">:</td>
                    <td class="td-value">{{ $mahasiswa->no_hp ?? '-' }}</td>
                </tr>
            </table>

            <p>
                Mahasiswa bersangkutan tercatat memiliki ketidakhadiran tanpa keterangan (Alpa) yang melampaui batas toleransi akademis, dengan rincian sebagai berikut:
            </p>

            <!-- Matriks Rincian Pelanggaran & Denda Kompensasi -->
            <table class="table-peringatan">
                <thead>
                    <tr>
                        <th>Total Jam Alpa</th>
                        <th>Kategori Peringatan</th>
                        <th>Kewajiban Denda Kompensasi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="font-size: 12.5pt; color: #b91c1c;">{{ $totalAlpaHours }} Jam Pelajaran</td>
                        <td class="highlight-sp">{{ $spTitle }}</td>
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

        <!-- Tanda Tangan Official (Ketua Jurusan) -->
        <table class="table-ttd">
            <tr>
                <td style="width: 55%;"></td>
                <td style="width: 45%; text-align: center; vertical-align: top;">
                    Padang, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}<br>
                    <strong>Ketua Jurusan Teknologi Informasi</strong>
                    <div class="ttd-ruang"></div>
                    <div class="ttd-nama">Rika Idmayanti, S.T., M.Kom.</div>
                    <div class="ttd-nip">NIP. 198007202005012002</div>
                </td>
            </tr>
        </table>

    </div>

</body>
</html>
