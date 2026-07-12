<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Traits;

trait HasUserAvatarViewComponent
{
    protected ?string $size = null;

    public function size(string $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }
}
