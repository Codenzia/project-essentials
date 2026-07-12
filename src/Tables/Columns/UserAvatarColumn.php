<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Tables\Columns;

use Codenzia\ProjectEssentials\Traits\HasUserAvatarViewComponent;
use Filament\Tables\Columns\Column;

class UserAvatarColumn extends Column
{
    use HasUserAvatarViewComponent;

    protected string $view = 'project-essentials::components.user-avatar';
}
