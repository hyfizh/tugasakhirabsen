<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemberitahuan {{ $spTitle }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; padding: 32px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0; }
        .header { text-align: center; border-bottom: 2px dashed #e2e8f0; padding-bottom: 20px; margin-bottom: 24px; }
        .header h2 { font-size: 16px; font-weight: 800; color: #0f172a; margin: 0; text-transform: uppercase; }
        .header h1 { font-size: 18px; font-weight: 900; color: #b91c1c; margin: 4px 0 0 0; text-transform: uppercase; }
        .alert-box { background-color: #fef2f2; border-left: 4px solid #ef4444; border-radius: 8px; padding: 16px; margin: 20px 0; }
        .details-table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 13px; }
        .details-table td { padding: 8px 12px; border-bottom: 1px solid #f1f5f9; }
        .details-table td.font-bold { font-weight: 700; color: #334155; }
        .footer { font-size: 11px; color: #94a3b8; text-align: center; margin-top: 32px; border-top: 1px solid #f1f5f9; padding-top: 16px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI</h2>
            <h2 style="color: #475569; font-size: 15px;">POLITEKNIK NEGERI PADANG</h2>
            <h1>PEMBERITAHUAN {{ strtoupper($spTitle) }}</h1>
        </div>

        <p style="font-size: 14px; color: #334155;">Yth. Sdr/i <strong>{{ $mahasiswa->nama_lengkap }}</strong> (NIM: {{ $mahasiswa->nim }}),</p>
        
        <div class="alert-box">
            <p style="font-size: 13px; color: #991b1b; margin: 0; font-weight: 600;">
                Berdasarkan evaluasi rekapitulasi presensi pada Jurusan Teknologi Informasi Politeknik Negeri Padang, Anda telah diterbitkan <strong>{{ $spTitle }}</strong> karena akumulasi jam ketidakhadiran (Alpa) tanpa keterangan resmi.
            </p>
        </div>

        <table class="details-table">
            <tr>
                <td class="font-bold" style="width: 40%;">Nama Mahasiswa</td>
                <td>{{ $mahasiswa->nama_lengkap }}</td>
            </tr>
            <tr>
                <td class="font-bold">NIM</td>
                <td>{{ $mahasiswa->nim }}</td>
            </tr>
            <tr>
                <td class="font-bold">Kelas</td>
                <td>{{ $mahasiswa->kelas->nama_kelas ?? '-' }}</td>
            </tr>
            <tr>
                <td class="font-bold">Total Akumulasi Alpa</td>
                <td style="color: #dc2626; font-weight: 800;">{{ $totalAlpaHours }} Jam</td>
            </tr>
            <tr>
                <td class="font-bold">Denda Jam Kompensasi</td>
                <td style="color: #b91c1c; font-weight: 800;">{{ $compensationPenalty }} Jam</td>
            </tr>
        </table>

        <p style="font-size: 13px; color: #475569; line-height: 1.6;">
            <strong>Lampiran Berkas:</strong> Dokumen fisik resmi <em>Surat Peringatan</em> bertanda tangan Ketua Jurusan telah terlampir dalam bentuk file PDF bersama email ini.
        </p>

        <p style="font-size: 13px; color: #475569; line-height: 1.6;">
            Harap segera menghadap Ketua Jurusan / Dosen Pembina Akademik dan melaksanakan kewajiban kompensasi sesuai peraturan yang berlaku.
        </p>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Jurusan Teknologi Informasi - Politeknik Negeri Padang.</p>
        </div>
    </div>
</body>
</html>
