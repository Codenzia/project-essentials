<?php

namespace Codenzia\ProjectEssentials;

use Codenzia\ProjectEssentials\Commands\ProjectEssentialsCommand;
use Codenzia\ProjectEssentials\Testing\TestsProjectEssentials;
use Codenzia\ProjectEssentials\View\Components\Progress;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Blade;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ProjectEssentialsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'project-essentials';

    public static string $viewNamespace = 'project-essentials';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('codenzia/project-essentials');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function boot(): void
    {
        // Ensure the real path to views is registered with the namespace
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'project-essentials');

        // Map component classes under the same namespace so <x-project-essentials::progress> uses the class
        Blade::componentNamespace('Codenzia\\ProjectEssentials\\View\\Components', 'project-essentials');

        // Optionally register short-named components:
        $this->loadViewComponentsAs('project-essentials', [
            Progress::class,
        ]);
    }

    public function packageRegistered(): void {}

    public function packageBooted(): void
    {
        parent::packageBooted();
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/project-essentials/{$file->getFilename()}"),
                ], 'project-essentials-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsProjectEssentials);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'codenzia/project-essentials';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('project-essentialst', __DIR__ . '/../resources/dist/components/project-essentialst.js'),
            // Css::make('project-essentialst-styles', __DIR__ . '/../resources/dist/project-essentialst.css'),
            // Js::make('project-essentialst-scripts', __DIR__ . '/../resources/dist/project-essentialst.js'),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            ProjectEssentialsCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_project-essentials_table',
        ];
    }
}
