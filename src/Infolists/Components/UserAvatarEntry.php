<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Infolists\Components;

use Codenzia\ProjectEssentials\Traits\HasUserAvatarViewComponent;
use Filament\Infolists\Components\Entry;

class UserAvatarEntry extends Entry
{
    use HasUserAvatarViewComponent;

    protected string $view = 'project-essentials::components.user-avatar';
}
