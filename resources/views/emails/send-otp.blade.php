<x-mail::message>
# Halo,

Anda menerima email ini karena ada permintaan reset password untuk akun Anda pada Sistem Absensi Mahasiswa Berbasis IoT.

Berikut adalah kode OTP Anda untuk melanjutkan proses reset password:

<x-mail::panel>
<h1 style="text-align: center; letter-spacing: 5px; font-size: 32px; font-weight: bold; color: #4f46e5; margin: 10px 0;">{{ $otp }}</h1>
</x-mail::panel>

*Kode OTP ini berlaku selama 15 menit. Harap tidak membagikan kode ini kepada siapapun.*

Jika Anda tidak meminta reset password, Anda dapat mengabaikan email ini.

Terima kasih,<br>
Tim Pengembang Sistem Absensi IoT
</x-mail::message>
