<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // Clear intended URL if it belongs to a different role prefix
        $intendedUrl = session('url.intended', '');
        if ($user->role === 'mahasiswa' && str_contains($intendedUrl, '/admin')) {
            session()->forget('url.intended');
        } elseif ($user->role === 'admin' && str_contains($intendedUrl, '/mahasiswa')) {
            session()->forget('url.intended');
        }

        if ($user->role === 'admin') {
            return redirect()->intended(route('admin.dashboard'));
        } elseif ($user->role === 'mahasiswa') {
            if (!$user->is_password_changed) {
                return redirect()->route('password.change')->with('warning', 'Anda harus mengubah password default Anda terlebih dahulu.');
            }
            return redirect()->intended(route('mahasiswa.dashboard'));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
