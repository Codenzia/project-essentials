<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Tables\Columns;

use Closure;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Database\Eloquent\Model;

class StateSwitcher extends ToggleColumn
{
    protected string $view = 'project-essentials::tables.columns.state-switcher';

    protected mixed $onState = 'Active';

    protected mixed $offState = 'Inactive';

    protected string $onLabel = 'Active';

    protected string $offLabel = 'Inactive';

    protected ?Closure $authorizeStateChangeUsing = null;

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
        return __($this->onLabel);
    }

    public function getOffLabel(): string
    {
        return __($this->offLabel);
    }

    /**
     * Register the host-side guard for an inline state change.
     *
     * The callback receives the `$record`, the `$state` about to be written and the
     * `$column` name, and must return true for the change to be persisted. Inline
     * column mutations bypass resource policies, so without this guard the column
     * refuses every change.
     *
     * Example:
     *   StateSwitcher::make('status')
     *       ->authorizeStateChangeUsing(fn (Model $record, mixed $state): bool =>
     *           auth()->user()->can('update', $record) && $record->canTransitionTo($state))
     */
    public function authorizeStateChangeUsing(?Closure $callback): static
    {
        $this->authorizeStateChangeUsing = $callback;

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->updateStateUsing(function ($state): array {
            $record = $this->getRecord();
            $column = $this->getName();

            if (! $record instanceof Model) {
                return ['error' => __('This record is no longer available.')];
            }

            $enumValue = $state ? $this->onState : $this->offState;

            if (! $this->isStateChangeAuthorized($record, $enumValue)) {
                return ['error' => __('You are not allowed to change this state.')];
            }

            $record->setAttribute($column, $enumValue);

            if (! $record->save()) {
                return ['error' => __('The state could not be saved.')];
            }

            return ['state' => $this->isActiveState($record->getAttribute($column))];
        });
    }

    /**
     * Whether the host has authorized this record + target state.
     * Denied by default: a column with no guard configured never writes.
     */
    protected function isStateChangeAuthorized(Model $record, mixed $state): bool
    {
        if ($this->authorizeStateChangeUsing === null) {
            return false;
        }

        return (bool) $this->evaluate($this->authorizeStateChangeUsing, [
            'record' => $record,
            'state' => $state,
            'column' => $this->getName(),
        ]);
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
