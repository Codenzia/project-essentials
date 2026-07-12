<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Infolists\Components;

use Codenzia\ProjectEssentials\Traits\HasColoredEnumViewComponent;
use Filament\Infolists\Components\TextEntry;

class ColoredEnumEntry extends TextEntry
{
    use HasColoredEnumViewComponent;
}
