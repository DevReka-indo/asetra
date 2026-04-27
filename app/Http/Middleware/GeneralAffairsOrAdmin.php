<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GeneralAffairsOrAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Allow if:
        // 1. Superadmin (role 1)
        // 2. General Affairs staff (section 12)
        if ($user && ($user->role_id_role == 1 || $user->section_id_section == 12)) {
            return $next($request);
        }

        abort(403, 'Akses hanya untuk superadmin atau staff bagian umum.');
    }
}
