<?php

namespace Codenzia\ProjectEssentials\Tables\Columns;

use Filament\Tables\Columns\ToggleColumn;

class StateSwitcher extends ToggleColumn
{
    protected string $view = 'project-essentials::tables.columns.state-switcher';

    protected mixed $onState = 'Active';

    protected mixed $offState = 'Inactive';

    protected string $onLabel = 'Active';

    protected string $offLabel = 'Inactive';

    public function onState(mixed $value): static
    {
        $this->onState = $value;

        return $this;
    }

    public function offState(mixed $value): static
    {
        $this->offState = $value;

        return $this;
    }

    public function onLabel(string $label): static
    {
        $this->onLabel = $label;

        return $this;
    }

    public function offLabel(string $label): static
    {
        $this->offLabel = $label;

        return $this;
    }

    public function getOnState(): mixed
    {
        return $this->onState;
    }

    public function getOffState(): mixed
    {
        return $this->offState;
    }

    public function getOnLabel(): string
    {
        return $this->onLabel;
    }

    public function getOffLabel(): string
    {
        return $this->offLabel;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->updateStateUsing(function ($state) {
            $record = $this->getRecord();
            $column = $this->getName();

            $enumValue = $state ? $this->onState : $this->offState;

            $record->setAttribute($column, $enumValue);
            $record->save();

            return $state;
        });
    }

    /**
     * Check if a given value represents the "on" state.
     */
    public function isActiveState(mixed $value): bool
    {
        $onState = $this->onState;

        // Handle enum comparison
        if (is_object($onState) && enum_exists(get_class($onState))) {
            if ($value instanceof \BackedEnum) {
                return $value === $onState;
            }

            return strtoupper((string) $value) === strtoupper((string) $onState->value);
        }

        // Handle string comparison
        if (is_string($value) && is_string($onState)) {
            return strtoupper($value) === strtoupper($onState);
        }

        return $value == $onState;
    }
}
