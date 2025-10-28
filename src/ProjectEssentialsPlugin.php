<?php

namespace Codenzia\ProjectEssentials;

use Filament\Contracts\Plugin;
use Filament\Panel;

class ProjectEssentialsPlugin implements Plugin
{
    public function getId(): string
    {
        return 'project-essentials';
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }
}
