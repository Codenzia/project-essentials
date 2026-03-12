<?php

namespace Codenzia\ProjectEssentials\Forms\Components;

use Codenzia\ProjectEssentials\Helpers\DateHelper;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;

/**
 * DatePickerWithHint
 *
 * Custom DatePicker that auto-applies the app's date format configuration:
 * - displayFormat from config('app.date_format')
 * - date format from config('app.date_format')
 * - Human-readable format hint (e.g. "yyyy-mm-dd")
 * - Non-native picker for consistency
 *
 * Supports hintPosition('top' | 'left' | 'right'):
 * - top   (default): hint icon with tooltip next to the field label
 * - left:  hint icon with tooltip as a prefix action inside the input
 * - right: hint icon with tooltip as a suffix action inside the input
 *
 * Usage:
 *   DatePickerWithHint::make('start_date')
 *   DatePickerWithHint::make('due_date')->hintPosition('left')
 */
class DatePickerWithHint extends DatePicker
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->displayFormat(config('app.date_format'))
            ->date(config('app.date_format'))
            ->hintIcon('heroicon-o-information-circle', tooltip: DateHelper::readableDateFormat())
            ->native(false);
    }

    /**
     * Move the format hint icon to a different position.
     *
     * 'top'   — default, hint icon with tooltip next to the label
     * 'left'  — hint icon with tooltip as a prefix action inside the input
     * 'right' — hint icon with tooltip as a suffix action inside the input
     */
    public function hintPosition(string $position): static
    {
        $tooltip = DateHelper::readableDateFormat();

        $hintAction = Action::make('date_format_hint')
            ->icon('heroicon-o-information-circle')
            ->color('gray')
            ->tooltip($tooltip)
            ->action(fn () => null);

        match ($position) {
            'left' => $this
                ->hintIcon(null)
                ->hint(null)
                ->prefixActions([$hintAction]),

            'right' => $this
                ->hintIcon(null)
                ->hint(null)
                ->suffixActions([$hintAction]),

            default => $this
                ->hintIcon('heroicon-o-information-circle', tooltip: $tooltip),
        };

        return $this;
    }
}
