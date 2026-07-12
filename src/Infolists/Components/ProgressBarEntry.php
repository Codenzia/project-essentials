<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Infolists\Components;

use Codenzia\ProjectEssentials\Traits\HasProgressBarViewComponent;
use Filament\Infolists\Components\Entry;

class ProgressBarEntry extends Entry
{
    use HasProgressBarViewComponent;

    protected string $view = 'project-essentials::tables.shared.progress-bar-shared';
}
