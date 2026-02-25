<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Forms\Components;

use Filament\Forms\Components\Select;

class IconPicker extends Select
{
    /**
     * Available icons grouped by category.
     *
     * @return array<string, string>
     */
    public static function icons(): array
    {
        return [
            // Education
            'heroicon-o-academic-cap' => __('Academic Cap'),
            'heroicon-o-book-open' => __('Book Open'),
            // Buildings & Places
            'heroicon-o-building-office' => __('Building Office'),
            'heroicon-o-building-office-2' => __('Building Office 2'),
            'heroicon-o-building-storefront' => __('Building Storefront'),
            'heroicon-o-building-library' => __('Building Library'),
            'heroicon-o-home' => __('Home'),
            'heroicon-o-home-modern' => __('Home Modern'),
            // Location & Maps
            'heroicon-o-map-pin' => __('Map Pin'),
            'heroicon-o-map' => __('Map'),
            'heroicon-o-globe-alt' => __('Globe'),
            // Transport
            'heroicon-o-truck' => __('Truck'),
            // Health & Safety
            'heroicon-o-heart' => __('Heart'),
            'heroicon-o-shield-check' => __('Shield Check'),
            // Shopping & Commerce
            'heroicon-o-shopping-bag' => __('Shopping Bag'),
            'heroicon-o-shopping-cart' => __('Shopping Cart'),
            'heroicon-o-banknotes' => __('Banknotes'),
            'heroicon-o-currency-dollar' => __('Currency Dollar'),
            // Communication
            'heroicon-o-phone' => __('Phone'),
            'heroicon-o-wifi' => __('Wifi'),
            'heroicon-o-signal' => __('Signal'),
            // Nature & Environment
            'heroicon-o-sun' => __('Sun'),
            'heroicon-o-fire' => __('Fire'),
            'heroicon-o-cloud' => __('Cloud'),
            // Utilities & Services
            'heroicon-o-bolt' => __('Bolt / Electricity'),
            'heroicon-o-wrench-screwdriver' => __('Wrench / Maintenance'),
            'heroicon-o-cog-6-tooth' => __('Cog / Settings'),
            'heroicon-o-key' => __('Key'),
            'heroicon-o-lock-closed' => __('Lock'),
            // Recreation
            'heroicon-o-film' => __('Film / Cinema'),
            'heroicon-o-musical-note' => __('Musical Note'),
            'heroicon-o-camera' => __('Camera'),
            // People
            'heroicon-o-user-group' => __('User Group'),
            'heroicon-o-users' => __('Users'),
            // Misc
            'heroicon-o-star' => __('Star'),
            'heroicon-o-sparkles' => __('Sparkles'),
            'heroicon-o-check-circle' => __('Check Circle'),
            'heroicon-o-information-circle' => __('Information Circle'),
            'heroicon-o-exclamation-triangle' => __('Warning Triangle'),
            'heroicon-o-clock' => __('Clock'),
            'heroicon-o-calendar' => __('Calendar'),
            'heroicon-o-chart-bar' => __('Chart Bar'),
            'heroicon-o-presentation-chart-line' => __('Chart Line'),
            'heroicon-o-rectangle-stack' => __('Rectangle Stack'),
            'heroicon-o-squares-2x2' => __('Squares Grid'),
            'heroicon-o-eye' => __('Eye'),
            'heroicon-o-hand-thumb-up' => __('Thumb Up'),
            'heroicon-o-lifebuoy' => __('Lifebuoy'),
            'heroicon-o-light-bulb' => __('Light Bulb'),
            'heroicon-o-puzzle-piece' => __('Puzzle Piece'),
            'heroicon-o-rocket-launch' => __('Rocket Launch'),
            'heroicon-o-trophy' => __('Trophy'),
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->searchable()
            ->allowHtml()
            ->options(fn (): array => self::buildHtmlOptions(self::icons()))
            ->getSearchResultsUsing(function (string $search): array {
                $filtered = collect(self::icons())
                    ->filter(fn (string $label): bool => str_contains(mb_strtolower($label), mb_strtolower($search)))
                    ->all();

                return self::buildHtmlOptions($filtered);
            })
            ->placeholder(__('Select an icon'));
    }

    /**
     * Build HTML option labels with rendered SVG icons.
     *
     * @param  array<string, string>  $icons
     * @return array<string, string>
     */
    protected static function buildHtmlOptions(array $icons): array
    {
        return collect($icons)
            ->mapWithKeys(fn (string $label, string $icon): array => [
                $icon => '<span class="flex items-center gap-2">' . svg($icon, 'w-5 h-5 shrink-0')->toHtml() . ' ' . $label . '</span>',
            ])
            ->all();
    }
}
