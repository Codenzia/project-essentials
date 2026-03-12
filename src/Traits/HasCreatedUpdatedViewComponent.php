<?php

namespace Codenzia\ProjectEssentials\Traits;

use Filament\Support\Concerns\HasExtraAttributes;

trait HasCreatedUpdatedViewComponent
{
    use HasExtraAttributes;
    // Maximum characters for usernames
    public int $limit = 20;

    public function setup(): void
    {
        // Set default label
        $this->label(__('Created & Updated'));
    }

    /**
     * Fluent setter for username limit
     */
    public function limit(int $length): static
    {
        $this->limit = $length;
        return $this;
    }
}
