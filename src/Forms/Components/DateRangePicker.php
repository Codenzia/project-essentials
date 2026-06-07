<?php

namespace Codenzia\ProjectEssentials\Forms\Components;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Carbon;

class DateRangePicker extends Field
{
    protected string $view = 'project-essentials::forms.components.date-range-picker';

    protected ?string $dateFormat = null;

    protected ?string $placeholder = null;

    protected ?string $fromColumn = null;

    protected ?string $toColumn = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->default(['from' => null, 'to' => null]);

        $this->afterStateHydrated(function (self $component, mixed $state): void {
            if ($component->getFromColumn() && $component->getToColumn()) {
                $record = $component->getRecord();
                $component->state([
                    'from' => self::normalizeDate($record?->getAttribute($component->getFromColumn())),
                    'to' => self::normalizeDate($record?->getAttribute($component->getToColumn())),
                ]);

                return;
            }

            if (! is_array($state)) {
                $component->state(['from' => null, 'to' => null]);
            }
        });

        $this->afterStateUpdated(function (self $component, Set $set, mixed $state): void {
            if (! $component->getFromColumn() || ! $component->getToColumn()) {
                return;
            }
            $value = is_array($state) ? $state : ['from' => null, 'to' => null];
            $set($component->getToColumn(), $value['to'] ?? null);
        });

        $self = $this;

        $this->dehydrateStateUsing(static function (mixed $state) use ($self): mixed {
            if (! $self->getFromColumn()) {
                return $state;
            }

            return is_array($state) ? ($state['from'] ?? null) : null;
        });
    }

    /**
     * Enable split-column mode: hydrate from two separate model attributes and dehydrate
     * back to two separate columns. Automatically enables live() so afterStateUpdated fires.
     *
     * Pair with forColumns() to avoid placing the Hidden field manually:
     *   ->components([...DateRangePicker::forColumns('start_date', 'end_date')])
     */
    public function fromColumn(string $column): static
    {
        $this->fromColumn = $column;
        $this->live();

        return $this;
    }

    public function toColumn(string $column): static
    {
        $this->toColumn = $column;

        return $this;
    }

    public function getFromColumn(): ?string
    {
        return $this->fromColumn;
    }

    public function getToColumn(): ?string
    {
        return $this->toColumn;
    }

    /**
     * Returns [Hidden::make($toColumn), DateRangePicker::make($fromColumn)->...]
     * for spreading into a schema. The Hidden carries the toColumn; the DateRangePicker
     * carries the fromColumn as its dehydrated value.
     *
     * Usage:
     *   ->components([
     *       ...DateRangePicker::forColumns('start_date', 'end_date', label: 'Duration'),
     *   ])
     */
    public static function forColumns(
        string $fromColumn,
        string $toColumn,
        ?string $label = null,
        bool $required = false,
    ): array {
        $hidden = Hidden::make($toColumn);

        $picker = static::make($fromColumn)
            ->fromColumn($fromColumn)
            ->toColumn($toColumn);

        if ($label !== null) {
            $picker->label($label);
        }

        if ($required) {
            $picker->required();
            $hidden->required();
        }

        return [$hidden, $picker];
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

    private static function normalizeDate(mixed $value): ?string
    {
        if ($value instanceof Carbon) {
            return $value->format('Y-m-d');
        }

        return $value !== null ? (string) $value : null;
    }
}
