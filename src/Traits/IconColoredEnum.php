<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Traits;

/**
 * Trait IconColoredEnum
 *
 * This trait provides a standardized way for Enum classes to:
 * - Assign human-readable labels
 * - Associate icons (e.g., Heroicons, custom SVG)
 * - Assign and retrieve Tailwind-compatible colors
 *
 * ✅ Features:
 * - Override static maps (`getLabels`, `getIcons`, `getColors`) in your Enum for custom mappings.
 * - Automatic color assignment from a predefined Tailwind palette if not overridden.
 * - Instance methods (`getLabel()`, `getIcon()`, `getColor()`) for working with enum instances.
 * - Static helpers for retrieving by value without instantiating the enum.
 * - Generates Tailwind-compatible CSS classes for quick UI styling.
 *
 * 🛠 Usage Example:
 * --------------------------------------------------
 * enum ProjectStatusEnum: string
 * {
 *     use IconColoredEnum;
 *
 *     case DRAFT = 'draft';
 *     case PUBLISHED = 'published';
 *     case ARCHIVED = 'archived';
 *
 *     public static function getLabels(): array
 *     {
 *         return [
 *             self::DRAFT->value     => 'Draft',
 *             self::PUBLISHED->value => 'Published',
 *             self::ARCHIVED->value  => 'Archived',
 *         ];
 *     }
 *
 *     public static function getIcons(): array
 *     {
 *         return [
 *             self::DRAFT->value     => 'heroicon-o-pencil-square',
 *             self::PUBLISHED->value => 'heroicon-o-check-circle',
 *             self::ARCHIVED->value  => 'heroicon-o-archive-box',
 *         ];
 *     }
 * }
 *
 * --------------------------------------------------
 * Example Calls:
 * $state = ProjectStatusEnum::DRAFT;
 * echo $state->getLabel();              // "Draft"
 * echo $state->getIcon();               // "heroicon-o-pencil-square"
 * echo $state->getColor();              // e.g., "blue"
 * echo $state->tailwindColorClass();    // "text-blue-500"
 * echo ProjectStatusEnum::label('draft'); // "Draft"
 */
trait IconColoredEnum
{
    /***************
     * INSTANCE METHODS
     ***************/

    public function getLabel(): ?string
    {
        return static::label($this->value);
    }

    public function getIcon(): ?string
    {
        return static::icon($this->value);
    }

    public function getColor($useTailwindColors = true): ?string
    {
        return static::color($this->value, $useTailwindColors);
    }

    public function tailwindColorClass(string $prefix = 'text', int $level = 500): string
    {
        return "{$prefix}-{$this->getColor()}-{$level}";
    }

    /***************
     * STATIC METHODS (LOOKUPS)
     ***************/

    public static function label(string $value): string
    {
        return static::getLabels()[$value]
            ?? ucfirst(str_replace(['-', '_'], ' ', $value));
    }

    public static function icon(string $value): string
    {
        return static::getIcons()[$value]
            ?? 'heroicon-o-question-mark-circle';
    }

    public static function color(string $value, bool $useTailwindColors = true): string
    {
        /**
         * Filament theme color palette for pseudo-random assignment.
         */
        static $availableColors = [
            'danger',
            'success',
            'warning',
            'info',
            'primary',
            'gray',
        ];

        $map = static::getColors();
        if (isset($map[$value])) {
            $color = $map[$value];
            if ($useTailwindColors) {
                // Map to Filament theme color names
                static $semanticToFilament = [
                    'success' => 'success',
                    'danger' => 'danger',
                    'warning' => 'warning',
                    'info' => 'info',
                    'primary' => 'primary',
                    'gray' => 'gray',
                ];

                return $semanticToFilament[$color] ?? $color;
            }

            return $color;
        }

        // Otherwise, assign a pseudo-random color based on hash
        $index = hexdec(substr(md5($value), 0, 2)) % count($availableColors);

        return $availableColors[$index];
    }

    /***************
     * STATIC MAP PROVIDERS (TO OVERRIDE IN YOUR OWN ENUM)
     ***************/

    public static function getLabels(): array
    {
        return [];
    }

    public static function getIcons(): array
    {
        return [];
    }

    public static function getColors(): array
    {
        return [];
    }

    /***************
     * BULK DATA HELPERS
     ***************/

    public static function labels(): array
    {
        return array_combine(
            array_column(static::cases(), 'value'),
            array_map(fn ($case) => $case->getLabel(), static::cases())
        );
    }

    public static function icons(): array
    {
        return array_combine(
            array_column(static::cases(), 'value'),
            array_map(fn ($case) => $case->getIcon(), static::cases())
        );
    }

    public static function colors(): array
    {
        return array_combine(
            array_column(static::cases(), 'value'),
            array_map(fn ($case) => $case->getColor(), static::cases())
        );
    }

    public static function options(): array
    {
        return array_map(function ($case) {
            return [
                'value' => $case->value,
                // Assuming your labels() method uses a custom getLabel(), reuse that here
                'label' => $case->getLabel(),
            ];
        }, static::cases());
    }

    /**
     * Get all cases except the ones specified in the $exclude array.
     */
    public static function casesExcept(array $exclude): array
    {
        $cases = array_filter(self::cases(), function ($case) use ($exclude) {
            return ! in_array($case, $exclude, true);
        });

        return array_values($cases);
    }

    /**
     * Get options (value/label array) except the ones specified in the $exclude array.
     * returns the same structure as options().
     */
    public static function optionsExcept(array $exclude): array
    {
        $filteredCases = static::casesExcept($exclude);

        return array_map(function ($case) {
            return [
                'value' => $case->value,
                'label' => $case->getLabel(),
            ];
        }, $filteredCases);
    }
}
