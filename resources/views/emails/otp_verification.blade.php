<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP Verifikasi Email</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 20px; }
        .container { max-width: 550px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; padding: 32px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0; }
        .header { text-align: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 20px; margin-bottom: 24px; }
        .header h1 { font-size: 20px; font-weight: 800; color: #0f172a; margin: 0; text-transform: uppercase; tracking-wide; }
        .header p { font-size: 12px; color: #64748b; margin-mt: 4px; }
        .otp-box { background-color: #f1f5f9; border: 2px dashed #cbd5e1; border-radius: 12px; text-align: center; padding: 20px; margin: 24px 0; }
        .otp-code { font-size: 36px; font-weight: 900; letter-spacing: 8px; color: #4f46e5; margin: 0; }
        .footer { font-size: 11px; color: #94a3b8; text-align: center; margin-top: 32px; border-t: 1px solid #f1f5f9; padding-top: 16px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>POLITEKNIK NEGERI PADANG</h1>
            <p>Sistem Presensi Mahasiswa Berbasis IoT (Absen TI)</p>
        </div>

        <h3 style="font-size: 16px; font-weight: 700; color: #0f172a;">Verifikasi Alamat Email</h3>
        <p style="font-size: 14px; color: #475569; line-height: 1.6;">
            Terima kasih telah melakukan pendaftaran email pada sistem <strong>Absen TI</strong>. Gunakan kode OTP berikut untuk memverifikasi dan mengaktifkan alamat email Anda:
        </p>

        <div class="otp-box">
            <p class="otp-code">{{ $otp }}</p>
            <p style="font-size: 12px; color: #64748b; margin-top: 8px; font-weight: 600;">Kode ini berlaku selama 10 menit</p>
        </div>

        <p style="font-size: 13px; color: #64748b; line-height: 1.5;">
            Jangan berikan kode ini kepada siapapun. Jika Anda tidak merasa melakukan permintaan verifikasi email ini, silakan abaikan pesan ini.
        </p>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Jurusan Teknologi Informasi - Politeknik Negeri Padang.</p>
        </div>
    </div>
</body>
</html>
