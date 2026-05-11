<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $appUrl = trim((string) config('app.url', ''));
        if ($appUrl !== '') {
            URL::forceRootUrl(rtrim($appUrl, '/'));

            $scheme = parse_url($appUrl, PHP_URL_SCHEME);
            if (is_string($scheme) && $scheme !== '') {
                URL::forceScheme($scheme);
            }
        }

        View::composer('layouts.app', function ($view) {
            if (in_array(session('user_role'), ['admin', 'manager'], true)) {
                try {
                    $count = DB::selectOne(
                        'SELECT COUNT(*) as cnt FROM "LEAD" l
                         WHERE COALESCE(l."ISDELETED", FALSE) = FALSE
                           AND (l."ASSIGNEDTO" IS NULL OR TRIM(CAST(l."ASSIGNEDTO" AS VARCHAR(50))) = \'\')
                           AND COALESCE((SELECT FIRST 1 UPPER(TRIM(la."STATUS"))
                                        FROM "LEAD_ACT" la
                                        WHERE la."LEADID" = l."LEADID"
                                        ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC
                                       ), \'CREATED\') IN (\'OPEN\', \'CREATED\')'
                    );
                    $view->with('adminNewInquiryCount', (int) ($count->cnt ?? $count->CNT ?? 0));
                } catch (\Throwable $e) {
                    $view->with('adminNewInquiryCount', 0);
                }
            }
        });
    }
}
