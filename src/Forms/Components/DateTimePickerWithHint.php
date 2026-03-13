<?php

namespace Codenzia\ProjectEssentials\Forms\Components;

use Codenzia\ProjectEssentials\Helpers\DateHelper;
use Filament\Forms\Components\DateTimePicker;

/**
 * DateTimePickerWithHint
 *
 * Custom DateTimePicker that auto-applies the app's datetime format configuration:
 * - displayFormat from config('app.datetime_format')
 * - Human-readable format hint (e.g. "yyyy-mm-dd hh:mm")
 * - Non-native picker for consistency
 *
 * Usage: DateTimePickerWithHint::make('start_at') — all formatting applied automatically.
 */
class DateTimePickerWithHint extends DateTimePicker
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->displayFormat(config('app.datetime_format', 'Y-m-d H:i'))
            ->hintIcon('heroicon-o-information-circle', tooltip: DateHelper::readableDateTimeFormat())
            ->native(false);
    }
}
