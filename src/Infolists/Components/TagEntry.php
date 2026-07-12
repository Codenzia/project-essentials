<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Infolists\Components;

use Filament\Infolists\Components\Entry;

class TagEntry extends Entry
{
    /**
     * The view used to render the entry.
     */
    protected string $view = 'project-essentials::components.tag-column';
}
