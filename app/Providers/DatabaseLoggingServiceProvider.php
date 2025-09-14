<?php

namespace App\Providers;

use App\Services\AuditService;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class DatabaseLoggingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Log de queries do banco de dados (apenas em desenvolvimento)
        if (app()->environment('local', 'development')) {
            DB::listen(function (QueryExecuted $query) {
                // Não logar queries muito frequentes para evitar spam
                $skipTables = ['sessions', 'cache', 'jobs', 'failed_jobs'];
                
                $shouldSkip = false;
                foreach ($skipTables as $table) {
                    if (str_contains(strtolower($query->sql), $table)) {
                        $shouldSkip = true;
                        break;
                    }
                }
                
                if (!$shouldSkip) {
                    $auditService = app(AuditService::class);
                    $auditService->logDatabaseQuery(
                        $query->sql,
                        $query->bindings,
                        $query->time
                    );
                }
            });
        }
    }
}
