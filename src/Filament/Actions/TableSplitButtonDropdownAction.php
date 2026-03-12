<?php

namespace Codenzia\ProjectEssentials\Filament\Actions;

use Filament\Actions\ActionGroup;
use Filament\Tables\Table;
use Filament\Actions\Concerns\InteractsWithRecord;

class TableSplitButtonDropdownAction extends ActionGroup
{
    use  InteractsWithRecord;
    protected ?Table $table;

    public function table(?Table $table): static
    {
        $this->table = $table;
        return $this;
    }

    public function getTable(): Table
    {
        return $this->table ?? $this->getGroup()?->getTable();
    }


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

    protected function setUp(): void
    {
        parent::setUp();

        //setup the chevron defaults
        $this->view('project-essentials::filament.components.split-button-dropdown-actions');
    }

    public function getActions(): array
    {
        $actions = parent::getActions();

        $table = $this->getTable();
        if ($table) {
            // Ensure the actions have a reference to the table
            // This is the core of the fix
            foreach ($actions as $action) {
                $action->table($table);
            }
        }
        return $actions;
    }
}
