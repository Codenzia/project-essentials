<?php

namespace Codenzia\ProjectEssentials\Tables\Columns;

use Filament\Tables\Columns\Column;

class PriorityColumn extends Column
{
    protected string $view = 'project-essentials::components.priority-display';

    protected ?string $textColor = null;

    public function textColor(?string $color): static
    {
        $this->textColor = $color;

        return $this;
    }

    public function getTextColor(): ?string
    {
        return $this->textColor;
    }
}
