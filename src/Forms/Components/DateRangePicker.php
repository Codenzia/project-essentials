<?php

namespace Codenzia\ProjectEssentials\Forms\Components;

use Filament\Forms\Components\Field;

class DateRangePicker extends Field
{
    protected string $view = 'project-essentials::forms.components.date-range-picker';

    protected ?string $dateFormat = null;

    protected ?string $placeholder = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->default(['from' => null, 'to' => null]);

        $this->afterStateHydrated(function (self $component, $state): void {
            if (! is_array($state)) {
                $component->state(['from' => null, 'to' => null]);
            }
        });
    }

    public function dateFormat(string $format): static
    {
        $this->dateFormat = $format;

        return $this;
    }

    public function getDateFormat(): string
    {
        return $this->dateFormat ?? config('app.date_format', 'd M, Y');
    }

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }
}
