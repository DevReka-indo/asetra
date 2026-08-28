<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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

        $route = $request->route();
        $routeName = $route ? $route->getName() : null;
        $isRepairManagementRoute = in_array($routeName, ['perbaikan.proses', 'perbaikan.selesai'], true);
        $isStockOpnameRoute = $routeName !== null
            && (str_starts_with($routeName, 'stock-opname.')
                || str_starts_with($routeName, 'pelaksanaan-opname.'));

        if ($isRepairManagementRoute) {
            Gate::authorize('manage_perbaikan_aset');

            return $next($request);
        }

        if ($isStockOpnameRoute) {
            if ($user && ($user->role_id_role == 1 || $user->isBagianUmum())) {
                return $next($request);
            }

            abort(403, 'Akses hanya untuk superadmin atau staff bagian umum (General Affairs).');
        }

        // untuk modul non-stock-opname: superadmin (role 1) atau
        // staff dengan permission 'manage_assets' diizinkan.
        if ($user && ($user->role_id_role == 1 || $user->hasPermission('manage_assets'))) {
            return $next($request);
        }

        abort(403, 'Akses hanya untuk superadmin atau staff bagian umum.');
    }
}
