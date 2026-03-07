<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                if ($user && $user->hasRole('Cliente')) {
                    // Clientes se quedan en la parte pública
                    return redirect()->route('welcome.index');
                }

                // Cualquier otro rol (actual o futuro) va al panel admin
                return redirect('/admin');
            }
        }

        return $next($request);
    }
}
