<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpVerificationMail;
use App\Models\Mahasiswa;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;

class EmailVerificationController extends Controller
{
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $email = trim($request->email);
        $user = auth()->user();

        // Check if email already used by another user
        $existingUser = User::where('email', $email)->where('id', '!=', $user->id)->first();
        if ($existingUser) {
            return redirect()->back()->with('error', 'Alamat email Gmail tersebut sudah digunakan oleh akun lain.');
        }

        // Generate 6-digit OTP
        $otp = rand(100000, 999999);

        // Store OTP & Pending Email in Session (expires in 10 mins)
        session([
            'pending_email'  => $email,
            'email_otp'      => $otp,
            'otp_expires_at' => now()->addMinutes(10),
            'otp_sent'       => true,
        ]);

        try {
            Mail::to($email)->send(new OtpVerificationMail($otp));
            return redirect()->back()->with('success', "Kode OTP 6-digit telah dikirimkan ke $email. Silakan periksa Inbox/Spam.");
        } catch (\Exception $e) {
            Log::error("Failed sending OTP email: " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengirim email. Silakan periksa koneksi internet.');
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $inputOtp     = trim($request->otp);
        $sessionOtp   = session('email_otp');
        $pendingEmail = session('pending_email');
        $expiresAt    = session('otp_expires_at');

        if (!$sessionOtp || !$pendingEmail || !$expiresAt) {
            return redirect()->back()->with('error', 'Sesi verifikasi OTP telah berakhir. Silakan minta kode OTP baru.');
        }

        if (now()->greaterThan($expiresAt)) {
            session()->forget(['email_otp', 'pending_email', 'otp_expires_at', 'otp_sent']);
            return redirect()->back()->with('error', 'Kode OTP telah kadaluarsa (lebih dari 10 menit). Silakan minta kode OTP baru.');
        }

        if ((string)$inputOtp !== (string)$sessionOtp) {
            return redirect()->back()->with('error', 'Kode OTP 6-digit yang Anda masukkan salah. Silakan periksa kembali.');
        }

        // OTP Verified Successfully! Update User & Mahasiswa Email
        $user = auth()->user();
        $user->email = $pendingEmail;
        $user->email_verified_at = now();
        $user->save();

        $mahasiswa = Mahasiswa::where('user_id', $user->id)->first();
        if ($mahasiswa) {
            $mahasiswa->email = $pendingEmail;
            $mahasiswa->save();
        }

        AuditLog::create([
            'tipe_log'   => 'EMAIL_VERIFIED',
            'deskripsi'  => "Mahasiswa {$user->name} berhasil melengkapi & memverifikasi alamat email: {$pendingEmail}",
            'ip_address' => $request->ip(),
        ]);

        // Clear Session OTP data
        session()->forget(['email_otp', 'pending_email', 'otp_expires_at', 'otp_sent']);

        return redirect()->back()->with('success', 'Email berhasil diverifikasi dan diaktifkan! Terima kasih telah melengkapi data akun Anda. ✅');
    }
}
