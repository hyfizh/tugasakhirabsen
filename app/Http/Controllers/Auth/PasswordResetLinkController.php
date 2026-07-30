<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\SendOtpMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Email tidak terdaftar di sistem kami.'],
            ]);
        }

        // Generate 6 digit OTP
        $otp = mt_rand(100000, 999999);

        // Save OTP to user
        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(15),
        ]);

        // Send OTP via Email
        Mail::to($user->email)->send(new SendOtpMail($otp));

        return redirect()->route('password.otp.verify', ['email' => $request->email])
                         ->with('status', 'Kode OTP telah dikirimkan ke email Anda.');
    }

    /**
     * Display the OTP verification and password reset form.
     */
    public function showOtpForm(Request $request): View
    {
        return view('auth.verify-otp', [
            'email' => $request->email,
        ]);
    }

    /**
     * Verify OTP and reset password.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp_code' => ['required', 'string', 'size:6'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Email tidak terdaftar.'],
            ]);
        }

        // Validate OTP
        if ($user->otp_code !== $request->otp_code) {
            throw ValidationException::withMessages([
                'otp_code' => ['Kode OTP yang Anda masukkan salah.'],
            ]);
        }

        // Check expiration
        if (now()->greaterThan($user->otp_expires_at)) {
            throw ValidationException::withMessages([
                'otp_code' => ['Kode OTP telah kedaluwarsa. Silakan minta kode baru.'],
            ]);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
            'otp_code' => null,
            'otp_expires_at' => null,
            'is_password_changed' => true,
        ]);

        return redirect()->route('login')
                         ->with('status', 'Password Anda berhasil diatur ulang. Silakan masuk menggunakan password baru.');
    }
}
