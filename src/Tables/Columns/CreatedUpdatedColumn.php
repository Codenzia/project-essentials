<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Tables\Columns;

use Codenzia\ProjectEssentials\Traits\HasCreatedUpdatedViewComponent;
use Filament\Tables\Columns\Column;

class CreatedUpdatedColumn extends Column
{
    use HasCreatedUpdatedViewComponent;

    protected string $view = 'project-essentials::tables.columns.created-updated-column';
}
