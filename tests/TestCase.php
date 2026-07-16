<?php

namespace Codenzia\ProjectEssentials\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Codenzia\ProjectEssentials\ProjectEssentialsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * Only this package's own service provider is listed explicitly.
     * Filament's providers + the Livewire / Blade / Icons providers
     * Filament depends on are auto-discovered via Composer's
     * extra.laravel.providers metadata when Testbench runs
     * `package:discover`. Keeps the TestCase compatible across Filament
     * v4 and v5 without hand-curating the import list.
     */
    protected function getPackageProviders($app): array
    {
        return [
            // Filament Support + Blade Icons must be explicit so Blade can
            // resolve <x-filament::badge> etc. in component render tests —
            // Testbench's package:discover alone doesn't get them there.
            BladeIconsServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            LivewireServiceProvider::class,
            SupportServiceProvider::class,
            ProjectEssentialsServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));
        $app['config']->set('app.date_format', 'd M, Y');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
