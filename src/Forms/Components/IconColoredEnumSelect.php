<?php

namespace Codenzia\ProjectEssentials\Forms\Components;

use Codenzia\ProjectEssentials\Helpers\TailwindHelper;
use Codenzia\ProjectEssentials\Traits\IconColoredEnum;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

/**
 * IconColoredEnumSelect
 *
 * A Filament Select that renders enum options with a colored icon and label.
 *
 * Works with ANY PHP 8.1+ backed enum that uses IconColoredEnum trait:
 * - static methods: label(string $value), icon(string $value), color(string $value)
 * - instance helpers: getLabel(), getIcon(), getColor(), tailwindColorClass()
 *
 * Features:
 * - Dropdown options show SVG icon + Tailwind color + label
 * - Selected value shows the same rich label
 * - Multi-select shows chips with colored icon & background
 * - Searchable by plain label
 * - Auto defaults to first enum case if none provided
 */
class IconColoredEnumSelect extends Select
{
    protected ?string $enumClass = null;

    protected ?array $optionsWithLabels = null;

    /**
     * Optionally accept an enum class at creation.
     */
    public static function make(?string $name = null, ?string $enumClass = null): static
    {
        $select = parent::make($name);

        if ($enumClass) {
            $select->enumClass($enumClass);
        }

        return $select;
    }

    /**
     * Assign enum class and configure select options.
     */
    public function enumClass(string $class): static
    {
        $this->enumClass = $class;

        if (! enum_exists($class)) {
            throw new InvalidArgumentException("{$class} must be a valid enum.");
        }

        if (! in_array(IconColoredEnum::class, class_uses($class), true)) {
            throw new InvalidArgumentException("{$class} must use the " . IconColoredEnum::class . ' trait.');
        }

        // Prepare simple searchable labels
        $this->optionsWithLabels = collect($class::cases())
            ->mapWithKeys(fn ($c) => [$c->value => $class::label($c->value)])
            ->toArray();

        // Render rich dropdown options HTML as strings
        $richOptions = [];
        foreach ($class::cases() as $case) {
            $value = $case->value;
            $label = $class::label($value);
            $icon = $class::icon($value);
            $color = $class::color($value);

            if (! $icon || ! $label || ! $color) {
                throw new InvalidArgumentException("Enum value '{$value}' is missing icon, label, or color in {$class}.");
            }
            $richOption = $this->renderOptionHtml(
                label: $class::label($value),
                icon: $class::icon($value),
                color: $class::color($value)
            )->toHtml(); // Convert HtmlString to string
            $richOptions[$value] = $richOption;
        }

        $this->options($richOptions)
            ->allowHtml()
            ->native(false)
            ->getSearchResultsUsing(
                fn (string $search) => collect($this->optionsWithLabels)
                    ->filter(fn (string $label) => Str::contains(mb_strtolower($label), mb_strtolower($search)))
                    ->keys()
                    ->mapWithKeys(fn ($key) => [$key => $richOptions[$key]])
                    ->toArray()
            )
            ->getOptionLabelUsing(function ($value) use ($class) {
                if ($value === null || $value === '') {
                    return new HtmlString('');
                }

                if (is_array($value)) {
                    $chips = array_map(fn ($val) => $this->renderChipHtml(
                        label: $class::label($val),
                        icon: $class::icon($val),
                        color: $class::color($val)
                    ), $value);

                    return new HtmlString(implode('', $chips));
                }

                return $this->renderOptionHtml(
                    label: $class::label($value),
                    icon: $class::icon($value),
                    color: $class::color($value)
                );
            });

        // Default to first enum case if none set
        $this->afterStateHydrated(function ($component, $state) use ($class) {
            if ($state === null) {
                $first = $class::cases()[0] ?? null;
                if ($first) {
                    $component->state($first->value);
                }
            }
        });

        return $this;
    }

    /**
     * Render a dropdown option: icon + colored label.
     */
    protected function renderOptionHtml(string $label, string $icon, string $color): HtmlString
    {
        $textClass = TailwindHelper::text($color);
        $iconSvg = $this->renderIconSvg($icon, "w-4 h-4 {$textClass}");

        return new HtmlString(
            <<<HTML
            <span class="flex items-center gap-2">
                {$iconSvg}
                <span class="{$textClass} font-medium">{$this->escape($label)}</span>
            </span>
            HTML
        );
    }

    /**
     * Render a selected-value chip: icon + background + text
     */
    protected function renderChipHtml(string $label, string $icon, string $color): HtmlString
    {
        $iconText = TailwindHelper::text($color, '600');
        $iconSvg = $this->renderIconSvg($icon, "w-4 h-4 {$iconText}");
        $bg = TailwindHelper::bg($color, '100');
        $text = TailwindHelper::text($color, '700');
        $bd = 'border ' . TailwindHelper::border($color, '200');

        return new HtmlString(
            <<<HTML
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full {$bg} {$text} {$bd} mr-1 mb-1">
                {$iconSvg}
                <span class="text-xs {$text}">{$this->escape($label)}</span>
            </span>
            HTML
        );
    }

    /**
     * Render Heroicon via Blade; fallback to colored dot.
     */
    protected function renderIconSvg(string $iconComponent, string $classes): string
    {
        try {
            return Blade::render(
                '<x-dynamic-component :component="$icon" :class="$classAttr" />',
                ['icon' => $iconComponent, 'classAttr' => $classes]
            );
        } catch (Throwable $e) {
            $colorClass = preg_match('/text-([a-z]+)-\d{3}/', $classes, $m) ? $m[1] : 'gray';
            $dotBg = TailwindHelper::bg($colorClass);

            return "<span class=\"inline-block w-2 h-2 rounded-full {$dotBg}\"></span>";
        }
    }

    /**
     * Simple HTML escape for labels.
     */
    protected function escape(string $value): string
    {
        return e($value);
    }
}
