<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Tables\Columns;

use Codenzia\ProjectEssentials\Traits\HasProgressBarViewComponent;
use Filament\Tables\Columns\Column;

class ProgressBarColumn extends Column
{
    use HasProgressBarViewComponent;

    protected string $view = 'project-essentials::tables.shared.progress-bar-shared';
}
