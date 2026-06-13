<?php

namespace Codenzia\ProjectEssentials\Traits;

use Filament\Support\Concerns\HasExtraAttributes;

trait HasCreatedUpdatedViewComponent
{
    use HasExtraAttributes;

    // Maximum characters for usernames
    public int $limit = 20;

    protected function setUp(): void
    {
        parent::setUp();

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
