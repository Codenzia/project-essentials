<?php

namespace Codenzia\ProjectEssentials\Tables\Columns;

use Codenzia\ProjectEssentials\Traits\HasColoredEnumViewComponent;
use Filament\Tables\Columns\TextColumn;

class ColoredEnumColumn extends TextColumn
{
    use HasColoredEnumViewComponent;
}
