<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

class CounterInput extends Field
{
    protected string $view = 'project-essentials::forms.components.counter-input';

    protected int | Closure | null $minValue = 0;

    protected int | Closure | null $maxValue = null;

    protected function setUp(): void
    {
        parent::setUp();

        $self = $this;

        // The +/- buttons and the number input are a convenience; the bounds are
        // enforced on the server, where a crafted request cannot skip them.
        $this->rule(static fn (): Closure => static function (string $attribute, mixed $value, Closure $fail) use ($self): void {
            if ($value === null || $value === '') {
                return;
            }

            if (! is_numeric($value) || (string) (int) $value !== (string) $value) {
                $fail(__('The :attribute must be a whole number.'));

                return;
            }

            $number = (int) $value;
            $min = $self->getMinValue();
            $max = $self->getMaxValue();

            if ($min !== null && $number < $min) {
                $fail(__('The :attribute must be at least :min.', ['min' => $min]));
            }

            if ($max !== null && $number > $max) {
                $fail(__('The :attribute must not be greater than :max.', ['max' => $max]));
            }
        });
    }

    /**
     * Lowest value the counter accepts. Pass null to remove the bound (defaults to 0).
     */
    public function minValue(int | Closure | null $value): static
    {
        $this->minValue = $value;

        return $this;
    }

    /**
     * Highest value the counter accepts. Defaults to unbounded.
     */
    public function maxValue(int | Closure | null $value): static
    {
        $this->maxValue = $value;

        return $this;
    }

    public function getMinValue(): ?int
    {
        $value = $this->evaluate($this->minValue);

        return $value === null ? null : (int) $value;
    }

    public function getMaxValue(): ?int
    {
        $value = $this->evaluate($this->maxValue);

        return $value === null ? null : (int) $value;
    }
}
