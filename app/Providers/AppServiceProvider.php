<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Share pending WFH count to admin sidebar (cached 30s for performance)
        View::composer('layouts.admin.sidebar', function ($view) {
            $pending = cache()->remember('pending_wfh_count', 30, function () {
                try {
                    return DB::table('wfh')->whereIn('status', ['pending_atasan','pending_admin'])->count();
                } catch (\Exception $e) {
                    return 0;
                }
            });
            $pendingAdmin = cache()->remember('pending_wfh_admin_count', 30, function () {
                try {
                    return DB::table('wfh')->where('status', 'pending_admin')->count();
                } catch (\Exception $e) {
                    return 0;
                }
            });
            $pendingLaporanAdmin = cache()->remember('pending_laporan_admin_count', 30, function () {
                try {
                    return DB::table('wfh')->where('laporan_status', 'pending_admin')->count();
                } catch (\Exception $e) {
                    return 0;
                }
            });
            $view->with('pendingWfhCount', $pending);
            $view->with('pendingWfhAdminCount', $pendingAdmin);
            $view->with('pendingLaporanAdminCount', $pendingLaporanAdmin);
        });

        View::composer('layouts.admin.tabler', function ($view) {
            try {
                $cnt = DB::table('wfh')->whereIn('status', ['pending_atasan','pending_admin'])->count();
                $view->with('pendingWfhCount', $cnt);
            } catch (\Exception $e) {
                $view->with('pendingWfhCount', 0);
            }
        });
    }
}
