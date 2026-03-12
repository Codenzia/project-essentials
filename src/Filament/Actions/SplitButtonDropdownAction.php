<?php

namespace Codenzia\ProjectEssentials\Filament\Actions;

use Filament\Actions\ActionGroup;

class SplitButtonDropdownAction extends ActionGroup
{
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

        // setup the chevron defaults
        $this->view('project-essentials::filament.components.split-button-dropdown-actions');
    }
}
