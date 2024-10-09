<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ViewPulse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedIds = [302, 303, 305, 306];

        if (auth()->check() && !in_array(auth()->user()->id, $allowedIds)) {
            return redirect(RouteServiceProvider::HOME);
        }
        return $next($request);
    }
}
