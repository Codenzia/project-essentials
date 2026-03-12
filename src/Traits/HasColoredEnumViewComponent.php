<?php

namespace Codenzia\ProjectEssentials\Traits;

use InvalidArgumentException;
use Codenzia\ProjectEssentials\Traits\IconColoredEnum;
use Filament\Support\Colors\Color;
use UnitEnum;


trait HasColoredEnumViewComponent
{
    protected ?string $enumClass = null;
    protected ?string $defaultText = null; // Store our fallback string

    /**
     * Set the fallback string if the value is not in the Enum.
     */
    public function defaultText(string|\Closure|null $value): static
    {
        $this->defaultText = $value;
        return $this;
    }

    /**
     * Set the enum class for this column.
     */

    public function enum(string $enumClass, bool $showText = true, bool $showIcon = true): static
    {
        if (!is_subclass_of($enumClass, UnitEnum::class)) {
            throw new InvalidArgumentException("{$enumClass} must be a PHP enum.");
        }

        if (!in_array(IconColoredEnum::class, class_uses($enumClass))) {
            throw new InvalidArgumentException("{$enumClass} must use IconColoredEnum trait.");
        }

        $this->enumClass = $enumClass;

        $this->tooltip(fn($state): ?string => $state instanceof UnitEnum ? $state->getLabel() : null);

        $this->formatStateUsing(function ($state) use ($showText): ?string {
            // 1. If it's a valid Enum instance, show the label
            if ($state instanceof UnitEnum) {
                return $showText ? $state?->getLabel() : '&nbsp;';
            }

            // 2. Fallback: If state is null/invalid, show the defaultText
            return $this->defaultText ?? $state;
        });

        $this->html();

        // Automatically set color, defaulting to 'gray' if state isn't an enum
        $this->color(fn($state): string|array|null => ($state instanceof UnitEnum) ? $state?->getColor(false) : Color::Gray);

        if ($showIcon) {
            $this->icon(fn($state): ?string => ($state instanceof UnitEnum) ? $state?->getIcon() : null);
        }

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->enumClass) {
            $this->sortable(fn($query, $direction) => $query->orderBy($this->getName(), $direction));
        }
    }
}
