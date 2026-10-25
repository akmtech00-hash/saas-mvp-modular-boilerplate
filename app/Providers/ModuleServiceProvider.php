<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register any module service providers.
     */
    public function register(): void
    {
        $modules = config('modules.modules', []);

        foreach ($modules as $moduleName => $config) {
            if (!empty($config['enabled']) && !empty($config['provider'])) {
                if (class_exists($config['provider'])) {
                    $this->app->register($config['provider']);
                }
            }
        }
    }

    /**
     * Bootstrap any module services, routes, and migrations.
     */
    public function boot(): void
    {
        $baseDir = app_path('Modules');
        if (!File::isDirectory($baseDir)) {
            return;
        }

        $modules = File::directories($baseDir);

        foreach ($modules as $modulePath) {
            $moduleName = basename($modulePath);
            $this->bootModuleRoutes($moduleName, $modulePath);
            $this->bootModuleMigrations($modulePath);
        }
    }

    /**
     * Discover and load module routes if present.
     */
    private function bootModuleRoutes(string $moduleName, string $modulePath): void
    {
        $webRoutes = $modulePath . '/Routes/web.php';
        if (File::exists($webRoutes)) {
            Route::middleware('web')
                ->group($webRoutes);
        }

        $apiRoutes = $modulePath . '/Routes/api.php';
        if (File::exists($apiRoutes)) {
            Route::prefix('api/v1')
                ->middleware('api')
                ->group($apiRoutes);
        }
    }

    /**
     * Discover and register module migrations if present.
     */
    private function bootModuleMigrations(string $modulePath): void
    {
        $migrationsPath = $modulePath . '/Database/Migrations';
        if (File::isDirectory($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);
        }
    }
}
