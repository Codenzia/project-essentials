<?php

namespace Codenzia\ProjectEssentials\Forms\Components;

use Filament\Forms\Components\Concerns\HasStep;
use Filament\Forms\Components\Field;

class PercentageSlider extends Field
{
    use HasStep;

    protected string $view = 'project-essentials::forms.components.percentage-slider';

    protected function setUp(): void
    {
        parent::setUp();

        $this->step = 1;

        // Initialize the field with a default value of 0 if none is set
        $this->afterStateHydrated(function ($component, $state) {
            if (is_null($state)) {
                $component->state(0);
            }
        });

        // Limit the value between 0 and 100
        $this->dehydrateStateUsing(function ($state) {
            return max(0, min(100, $state));
        });
    }
}
