<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Tests\Fixtures;

use Codenzia\ProjectEssentials\Traits\CanToggleColumns;

class ToggleColumnsHost
{
    use CanToggleColumns;

    /**
     * Stand in for Livewire's dispatch() so the trait can be exercised
     * outside of a real Livewire component.
     */
    public function dispatch(string $event, ...$params): void
    {
        // no-op for tests
    }
}
