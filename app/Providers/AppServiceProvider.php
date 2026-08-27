<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Permissions exposed through Laravel's authorization abilities.
     *
     * @var list<string>
     */
    public const PERMISSION_ABILITIES = [
        'view_dashboard_ga',
        'manage_lokasi_aset',
        'manage_jenis_kategori',
        'manage_kategori_aset',
        'manage_assets',
        'manage_log_aset',
        'manage_stock_opname',
        'manage_perbaikan_aset',
        'manage_pemulihan',
        'manage_organization',
        'manage_users',
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(static function (User $user): ?bool {
            return $user->role_id_role === 1 ? true : null;
        });

        foreach (self::PERMISSION_ABILITIES as $ability) {
            Gate::define($ability, static fn (User $user): bool => $user->hasPermission($ability));
        }
    }
}
