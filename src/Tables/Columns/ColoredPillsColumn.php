<?php

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

        if (! in_array(IconColoredEnum::class, class_uses($enumClass))) {
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
            return (string) ($this->itemColorUsing)($item);
        }

        if ($this->enumClass) {
            $enumInstance = $this->resolveEnumInstance($item);
            if ($enumInstance) {
                $color = $enumInstance->getColor(true);

                return static::tailwindColorToBadgeClasses($color);
            }
        }

        return 'bg-gray-100 border-gray-300 text-gray-600 dark:bg-gray-500/15 dark:border-gray-400/50 dark:text-gray-400';
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
     * Convert a Tailwind color name (from IconColoredEnum) to badge CSS classes
     * with proper light and dark mode support.
     */
    public static function tailwindColorToBadgeClasses(string $color): string
    {
        return match ($color) {
            'danger', 'red' => 'bg-red-100 border-red-300 text-red-700 dark:bg-red-500/15 dark:border-red-400/50 dark:text-red-400',
            'success', 'green', 'emerald' => 'bg-emerald-100 border-emerald-300 text-emerald-700 dark:bg-green-500/15 dark:border-green-400/50 dark:text-green-400',
            'warning', 'yellow', 'amber' => 'bg-amber-100 border-amber-300 text-amber-700 dark:bg-amber-500/15 dark:border-amber-400/50 dark:text-amber-400',
            'info', 'blue' => 'bg-blue-100 border-blue-300 text-blue-700 dark:bg-blue-500/15 dark:border-blue-400/50 dark:text-blue-400',
            'indigo' => 'bg-indigo-100 border-indigo-300 text-indigo-700 dark:bg-indigo-500/15 dark:border-indigo-400/50 dark:text-indigo-400',
            'purple' => 'bg-purple-100 border-purple-300 text-purple-700 dark:bg-purple-500/15 dark:border-purple-400/50 dark:text-purple-400',
            'pink', 'rose' => 'bg-pink-100 border-pink-300 text-pink-700 dark:bg-pink-500/15 dark:border-pink-400/50 dark:text-pink-400',
            'teal', 'cyan' => 'bg-teal-100 border-teal-300 text-teal-700 dark:bg-teal-500/15 dark:border-teal-400/50 dark:text-teal-400',
            'lime' => 'bg-lime-100 border-lime-300 text-lime-700 dark:bg-lime-500/15 dark:border-lime-400/50 dark:text-lime-400',
            'fuchsia' => 'bg-fuchsia-100 border-fuchsia-300 text-fuchsia-700 dark:bg-fuchsia-500/15 dark:border-fuchsia-400/50 dark:text-fuchsia-400',
            default => 'bg-gray-100 border-gray-300 text-gray-600 dark:bg-gray-500/15 dark:border-gray-400/50 dark:text-gray-400',
        };
    }
}
