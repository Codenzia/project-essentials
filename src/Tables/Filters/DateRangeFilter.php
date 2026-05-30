<?php

namespace Codenzia\ProjectEssentials\Tables\Filters;

use Carbon\Carbon;
use Codenzia\ProjectEssentials\Forms\Components\DateRangePicker;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class DateRangeFilter extends Filter
{
    protected ?string $column = null;

    protected ?string $placeholder = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('');

        $this->schema(fn (): array => [
            DateRangePicker::make($this->getName())
                ->label('')
                ->placeholder($this->getPlaceholder()),
        ]);

        $this->query(function (Builder $query, array $data): Builder {
            $range = $data[$this->getName()] ?? [];

            return $query
                ->when($range['from'] ?? null, fn (Builder $query, $date) => $query->whereDate($this->getColumn(), '>=', $date))
                ->when($range['to'] ?? null, fn (Builder $query, $date) => $query->whereDate($this->getColumn(), '<=', $date));
        });

        $this->indicateUsing(function (array $data): array {
            $range = $data[$this->getName()] ?? [];
            $from = $range['from'] ?? null;
            $to = $range['to'] ?? null;

            if (! $from && ! $to) {
                return [];
            }

            $format = config('app.date_format', 'd M, Y');
            $label = $this->getPlaceholder() . ': ';

            if ($from && $to) {
                $label .= Carbon::parse($from)->format($format) . ' - ' . Carbon::parse($to)->format($format);
            } elseif ($from) {
                $label .= __('From') . ' ' . Carbon::parse($from)->format($format);
            } else {
                $label .= __('Until') . ' ' . Carbon::parse($to)->format($format);
            }

            return [Indicator::make($label)->removeField($this->getName())];
        });
    }

    public function column(string $column): static
    {
        $this->column = $column;

        return $this;
    }

    public function getColumn(): string
    {
        return $this->column ?? $this->getName();
    }

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function getPlaceholder(): string
    {
        return $this->placeholder ?? $this->getLabel() ?: $this->getName();
    }
}
