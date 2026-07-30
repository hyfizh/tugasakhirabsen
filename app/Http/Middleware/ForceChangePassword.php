<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceChangePassword
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() &&
            $request->user()->isMahasiswa() &&
            !$request->user()->is_password_changed &&
            !$request->routeIs('password.change', 'password.change.update', 'logout')
        ) {
            return redirect()->route('password.change')->with('warning', 'Anda harus mengubah password default Anda terlebih dahulu.');
        }

        return $next($request);
    }
}
