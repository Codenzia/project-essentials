<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Filament\Actions;

use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithRecord;
use Filament\Tables\Table;

class SplitButtonDropdownAction extends ActionGroup
{
    use InteractsWithRecord;

    protected ?Table $table = null;

    public function __construct(array $actions)
    {
        parent::__construct($actions);
    }

    public static function make(array $actions): static
    {
        $static = app(static::class, ['actions' => $actions]);
        $static->configure();

        return $static;
    }

    public function table(?Table $table): static
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Returns the table context if available (e.g. when used inside table headerActions).
     * Returns null when used as a page-level header action.
     */
    public function getTable(): ?Table
    {
        return $this->table ?? $this->getGroup()?->getTable();
    }

    protected function setUp(): void
    {
        parent::setUp();

        // setup the chevron defaults
        $this->view('project-essentials::filament.components.split-button-dropdown-actions');
    }

    public function getActions(): array
    {
        $actions = parent::getActions();

        $table = $this->getTable();
        if ($table) {
            // Inject table context into child actions when used in a table
            foreach ($actions as $action) {
                $action->table($table);
            }
        }

        return $actions;
    }
}
