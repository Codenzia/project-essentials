<?php

namespace Codenzia\ProjectEssentials\Forms\Components;

use Codenzia\ProjectEssentials\Helpers\DateHelper;
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
 * Usage: DatePickerWithHint::make('start_date') — all formatting applied automatically.
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
}
