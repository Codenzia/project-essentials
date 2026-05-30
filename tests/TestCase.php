<?php

namespace Codenzia\ProjectEssentials\Tests;

use Codenzia\ProjectEssentials\ProjectEssentialsServiceProvider;
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
}
