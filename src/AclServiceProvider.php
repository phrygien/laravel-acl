<?php

namespace MecenePhrygien\LaravelAcl;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

class AclServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/acl.php', 'acl');
        $this->app->singleton('acl', fn() => new AclManager());
    }

    public function boot(): void
    {
        $this->registerPublishables();
        $this->registerMiddleware();
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'acl');
        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        if (config('acl.ui.enabled', true)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }
    }

    private function registerPublishables(): void
    {
        $this->publishes([
            __DIR__.'/../config/acl.php' => config_path('acl.php'),
        ], 'acl-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'acl-migrations');

        $this->publishes([
            __DIR__.'/../database/seeders' => database_path('seeders'),
        ], 'acl-seeders');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/acl'),
        ], 'acl-views');
    }

    private function registerMiddleware(): void
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('role', Middleware\RoleMiddleware::class);
        $router->aliasMiddleware('permission', Middleware\PermissionMiddleware::class);
        $router->aliasMiddleware('role_or_permission', Middleware\RoleOrPermissionMiddleware::class);
    }
}