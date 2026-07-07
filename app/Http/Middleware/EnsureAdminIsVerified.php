<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware khusus admin - dipisah dari alias "verified" bawaan Laravel
 * agar niatnya eksplisit: verifikasi email hanya diwajibkan untuk role admin,
 * tidak untuk player (lihat keputusan Tahap 13).
 */
class EnsureAdminIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
