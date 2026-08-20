<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccessCodeIsValid
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            return $next($request);
        }

        $cookie = $request->cookie('access_granted');

        if (! is_string($cookie) || ! hash_equals(static::expectedCookieValue(), $cookie)) {
            return redirect()->route('access.show');
        }

        return $next($request);
    }

    /**
     * The cookie value proving the access code was entered correctly.
     *
     * Signed with the app key so rotating the access code (or the app key)
     * automatically invalidates every previously granted cookie.
     */
    public static function expectedCookieValue(): string
    {
        return hash_hmac('sha256', config('access.code'), config('app.key'));
    }
}
