<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Traits;

use Filament\Support\Colors\Color;
use InvalidArgumentException;
use UnitEnum;

trait HasColoredEnumViewComponent
{
    protected ?string $enumClass = null;

    protected string | \Closure | null $defaultText = null; // Store our fallback string

    /**
     * Set the fallback string if the value is not in the Enum.
     */
    public function defaultText(string | \Closure | null $value): static
    {
        $this->defaultText = $value;

        return $this;
    }

    /**
     * Set the enum class for this column.
     */
    public function enum(string $enumClass, bool $showText = true, bool $showIcon = true): static
    {
        if (! is_subclass_of($enumClass, UnitEnum::class)) {
            throw new InvalidArgumentException("{$enumClass} must be a PHP enum.");
        }

        if (! in_array(IconColoredEnum::class, class_uses_recursive($enumClass))) {
            throw new InvalidArgumentException("{$enumClass} must use IconColoredEnum trait.");
        }

        $this->enumClass = $enumClass;

        $this->tooltip(fn ($state): ?string => $state instanceof UnitEnum ? $state->getLabel() : null);

        $this->formatStateUsing(function ($state) use ($showText): ?string {
            if ($state instanceof UnitEnum) {
                return $showText ? $state?->getLabel() : null;
            }

            return $this->evaluate($this->defaultText) ?? $state;
        });

        // Automatically set color, defaulting to 'gray' if state isn't an enum
        $this->color(fn ($state): string | array | null => ($state instanceof UnitEnum) ? $state?->getColor(false) : Color::Gray);

        if ($showIcon) {
            $this->icon(fn ($state): ?string => ($state instanceof UnitEnum) ? $state?->getIcon() : null);
        }

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->enumClass) {
            $this->sortable(fn ($query, $direction) => $query->orderBy($this->getName(), $direction));
        }
    }
}
