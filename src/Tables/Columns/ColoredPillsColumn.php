<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Tables\Columns;

use Closure;
use Codenzia\ProjectEssentials\Traits\IconColoredEnum;
use Filament\Tables\Columns\Column;
use Illuminate\Support\Collection;
use UnitEnum;

class ColoredPillsColumn extends Column
{
    protected string $view = 'project-essentials::components.colored-pills';

    protected int $visibleLimit = 3;

    /** Resolve items from the record */
    protected ?Closure $itemsResolver = null;

    /** Get display label from each item */
    protected string | Closure $itemLabelUsing = 'name';

    /** Get CSS classes for each item's badge */
    protected ?Closure $itemColorUsing = null;

    /** Get size class for each item */
    protected ?Closure $itemSizeUsing = null;

    /** Get tooltip string for each item */
    protected ?Closure $itemTooltipUsing = null;

    /** Label shown when collection is empty */
    protected string $emptyLabel = 'No Items';

    /** Singular|plural label for hover card header */
    protected string $hoverLabel = ':count more item|:count more items';

    /** Show subtitle in hover card items */
    protected bool $showSubtitleInHover = false;

    /** Get subtitle for hover card items */
    protected ?Closure $itemSubtitleUsing = null;

    /** IconColoredEnum class for automatic color/label/tooltip resolution */
    protected ?string $enumClass = null;

    /** Attribute path to read the enum value from each item (e.g. 'pivot.proficiency') */
    protected ?string $enumAttribute = null;

    // ── Fluent setters ──────────────────────────────

    public function visibleLimit(int $limit): static
    {
        $this->visibleLimit = $limit;

        return $this;
    }

    public function items(Closure $resolver): static
    {
        $this->itemsResolver = $resolver;

        return $this;
    }

    public function itemLabel(string | Closure $accessor): static
    {
        $this->itemLabelUsing = $accessor;

        return $this;
    }

    public function itemColor(Closure $callback): static
    {
        $this->itemColorUsing = $callback;

        return $this;
    }

    public function itemSize(Closure $callback): static
    {
        $this->itemSizeUsing = $callback;

        return $this;
    }

    public function itemTooltip(Closure $callback): static
    {
        $this->itemTooltipUsing = $callback;

        return $this;
    }

    public function itemSubtitle(Closure $callback): static
    {
        $this->itemSubtitleUsing = $callback;
        $this->showSubtitleInHover = true;

        return $this;
    }

    public function emptyLabel(string $label): static
    {
        $this->emptyLabel = $label;

        return $this;
    }

    public function hoverLabel(string $label): static
    {
        $this->hoverLabel = $label;

        return $this;
    }

    /**
     * Use an IconColoredEnum to automatically resolve colors, tooltips, and subtitles.
     *
     * @param  string  $enumClass  The enum class (must use IconColoredEnum trait)
     * @param  string  $attribute  Dot-notation path to the enum value on each item (e.g. 'pivot.proficiency')
     */
    public function enum(string $enumClass, string $attribute): static
    {
        if (! is_subclass_of($enumClass, UnitEnum::class)) {
            throw new \InvalidArgumentException("{$enumClass} must be a PHP enum.");
        }

        if (! in_array(IconColoredEnum::class, class_uses_recursive($enumClass))) {
            throw new \InvalidArgumentException("{$enumClass} must use the IconColoredEnum trait.");
        }

        $this->enumClass = $enumClass;
        $this->enumAttribute = $attribute;

        return $this;
    }

    // ── Getters (used by the blade view) ────────────

    public function getVisibleLimit(): int
    {
        return $this->visibleLimit;
    }

    public function resolveItems($record): Collection
    {
        if ($this->itemsResolver) {
            $items = ($this->itemsResolver)($record);
        } else {
            $items = $record->{$this->getName()} ?? collect();
        }

        return collect($items);
    }

    public function resolveItemLabel($item): string
    {
        if ($this->itemLabelUsing instanceof Closure) {
            return (string) ($this->itemLabelUsing)($item);
        }

        return (string) (is_object($item) ? ($item->{$this->itemLabelUsing} ?? '') : ($item[$this->itemLabelUsing] ?? $item));
    }

    public function resolveItemColor($item): string
    {
        if ($this->itemColorUsing) {
            $color = (string) ($this->itemColorUsing)($item);

            // If it's a short color name (no spaces = not full CSS classes), convert it
            if (! str_contains($color, ' ')) {
                return static::tailwindColorToBadgeClasses($color);
            }

            return $color;
        }

        if ($this->enumClass) {
            $enumInstance = $this->resolveEnumInstance($item);
            if ($enumInstance) {
                $color = $enumInstance->getColor(true);

                return static::tailwindColorToBadgeClasses($color);
            }
        }

        return static::tailwindColorToBadgeClasses('gray');
    }

    public function resolveItemSize($item): string
    {
        if ($this->itemSizeUsing) {
            return (string) ($this->itemSizeUsing)($item);
        }

        return 'text-xs';
    }

    public function resolveItemTooltip($item): ?string
    {
        if ($this->itemTooltipUsing) {
            return ($this->itemTooltipUsing)($item);
        }

        if ($this->enumClass) {
            $enumInstance = $this->resolveEnumInstance($item);
            $label = $this->resolveItemLabel($item);
            if ($enumInstance) {
                return $label . ' — ' . $enumInstance->getLabel();
            }
        }

        return null;
    }

    public function resolveItemSubtitle($item): ?string
    {
        if ($this->itemSubtitleUsing) {
            return ($this->itemSubtitleUsing)($item);
        }

        if ($this->enumClass) {
            $enumInstance = $this->resolveEnumInstance($item);
            if ($enumInstance) {
                $this->showSubtitleInHover = true;

                return $enumInstance->getLabel();
            }
        }

        return null;
    }

    public function getEmptyLabel(): string
    {
        return $this->emptyLabel;
    }

    public function getHoverLabel(): string
    {
        return $this->hoverLabel;
    }

    public function getShowSubtitleInHover(): bool
    {
        return $this->showSubtitleInHover;
    }

    // ── Enum helpers ────────────────────────────────

    /**
     * Resolve an enum instance from an item using the configured attribute path.
     */
    protected function resolveEnumInstance($item): ?UnitEnum
    {
        $value = data_get($item, $this->enumAttribute);

        if ($value instanceof UnitEnum) {
            return $value;
        }

        if (is_string($value) || is_int($value)) {
            return $this->enumClass::tryFrom($value);
        }

        return null;
    }

    /**
     * Convert a semantic color name (from IconColoredEnum) to badge CSS classes
     * using Filament's theme color variables for proper light/dark mode support.
     *
     * Supported Filament theme colors: danger, success, warning, info, primary, gray.
     */
    public static function tailwindColorToBadgeClasses(string $color): string
    {
        return match ($color) {
            'danger', 'red', 'rose', 'pink' => 'bg-transparent border-danger-400 text-danger-600 dark:border-danger-400 dark:text-danger-400',
            'success', 'green', 'emerald' => 'bg-transparent border-success-400 text-success-600 dark:border-success-400 dark:text-success-400',
            'warning', 'yellow', 'amber', 'orange' => 'bg-transparent border-warning-400 text-warning-600 dark:border-warning-400 dark:text-warning-400',
            'info', 'blue', 'indigo', 'cyan', 'teal' => 'bg-transparent border-info-400 text-info-600 dark:border-info-400 dark:text-info-400',
            'primary', 'purple', 'fuchsia', 'lime' => 'bg-transparent border-primary-400 text-primary-600 dark:border-primary-400 dark:text-primary-400',
            default => 'bg-transparent border-gray-400 text-gray-600 dark:border-gray-400 dark:text-gray-400',
        };
    }
}
