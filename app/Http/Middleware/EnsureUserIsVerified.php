<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsVerified
{
    /**
     * Проверка, что пользователь верифицирован.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Если пользователь не авторизован или не верифицирован
        if (!Auth::check() || !Auth::user()->is_verified) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return redirect()->route('auth.login');
        }

        return $next($request);
    }
}
