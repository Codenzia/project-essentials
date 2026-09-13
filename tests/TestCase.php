<?php

namespace Codenzia\ProjectEssentials\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Codenzia\ProjectEssentials\ProjectEssentialsServiceProvider;
use Codenzia\ProjectEssentials\Tests\Fixtures\TestPanelProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Livewire::test()/HTTP calls in Testbench do not run the web middleware
        // that shares a populated error bag into views. Livewire v4's getErrorBag()
        // reads app('view')->shared('errors')->getMessages() on first render, so an
        // error bag WITHOUT a `default` MessageBag yields null and Filament tables
        // blow up. Share one that has a real default bag.
        $this->app['view']->share('errors', (new ViewErrorBag)->put('default', new MessageBag));
    }

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
            ActionsServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            SchemasServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            FilamentServiceProvider::class,
            TestPanelProvider::class,
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
