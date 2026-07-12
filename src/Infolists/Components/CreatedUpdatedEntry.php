<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Infolists\Components;

use Codenzia\ProjectEssentials\Traits\HasCreatedUpdatedViewComponent;
use Filament\Infolists\Components\Entry;

class CreatedUpdatedEntry extends Entry
{
    use HasCreatedUpdatedViewComponent;

    protected string $view = 'project-essentials::tables.columns.created-updated-column';
}
