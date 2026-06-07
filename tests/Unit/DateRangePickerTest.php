<?php

use Codenzia\ProjectEssentials\Forms\Components\DateRangePicker;
use Filament\Forms\Components\Hidden;

it('can be instantiated', function () {
    $component = DateRangePicker::make('date_range');

    expect($component)->toBeInstanceOf(DateRangePicker::class);
    expect($component->getName())->toBe('date_range');
});

it('has the correct view', function () {
    $component = DateRangePicker::make('date_range');

    expect($component->getView())->toBe('project-essentials::forms.components.date-range-picker');
});

it('has default state as array with from and to keys', function () {
    $component = DateRangePicker::make('date_range');

    expect($component->getDefaultState())->toBe(['from' => null, 'to' => null]);
});

it('returns default date format from config', function () {
    $component = DateRangePicker::make('date_range');

    expect($component->getDateFormat())->toBe('d M, Y');
});

it('allows custom date format', function () {
    $component = DateRangePicker::make('date_range')
        ->dateFormat('Y-m-d');

    expect($component->getDateFormat())->toBe('Y-m-d');
});

it('returns null placeholder by default', function () {
    $component = DateRangePicker::make('date_range');

    expect($component->getPlaceholder())->toBeNull();
});

it('allows custom placeholder', function () {
    $component = DateRangePicker::make('date_range')
        ->placeholder('Pick a range');

    expect($component->getPlaceholder())->toBe('Pick a range');
});

it('supports fluent chaining', function () {
    $component = DateRangePicker::make('date_range')
        ->dateFormat('Y-m-d')
        ->placeholder('Select dates');

    expect($component->getDateFormat())->toBe('Y-m-d');
    expect($component->getPlaceholder())->toBe('Select dates');
});

// Split-column mode

it('stores fromColumn and toColumn via fluent setters', function () {
    $component = DateRangePicker::make('date_range')
        ->fromColumn('start_date')
        ->toColumn('end_date');

    expect($component->getFromColumn())->toBe('start_date');
    expect($component->getToColumn())->toBe('end_date');
});

it('returns null for fromColumn and toColumn when not set', function () {
    $component = DateRangePicker::make('date_range');

    expect($component->getFromColumn())->toBeNull();
    expect($component->getToColumn())->toBeNull();
});

it('calling fromColumn marks the component as live so afterStateUpdated fires', function () {
    // isLive() needs a mounted container; read the protected $isLive flag directly.
    $component = DateRangePicker::make('date_range')
        ->fromColumn('start_date');

    $isLive = (fn () => $this->isLive)->call($component);

    expect($isLive)->toBeTrue();
});

it('forColumns returns array of two elements', function () {
    $result = DateRangePicker::forColumns('start_date', 'end_date');

    expect($result)->toBeArray()->toHaveCount(2);
});

it('forColumns first element is a Hidden field for the toColumn', function () {
    $result = DateRangePicker::forColumns('start_date', 'end_date');

    expect($result[0])->toBeInstanceOf(Hidden::class);
    expect($result[0]->getName())->toBe('end_date');
});

it('forColumns second element is a DateRangePicker named after the fromColumn', function () {
    $result = DateRangePicker::forColumns('start_date', 'end_date');

    expect($result[1])->toBeInstanceOf(DateRangePicker::class);
    expect($result[1]->getName())->toBe('start_date');
    expect($result[1]->getFromColumn())->toBe('start_date');
    expect($result[1]->getToColumn())->toBe('end_date');
});

it('forColumns accepts an optional label', function () {
    $result = DateRangePicker::forColumns('start_date', 'end_date', 'Project Duration');

    expect($result[1]->getLabel())->toBe('Project Duration');
});

it('forColumns without explicit label auto-generates one from the field name', function () {
    $result = DateRangePicker::forColumns('start_date', 'end_date');

    // Filament generates a label from the field name; we just verify it is a non-empty string.
    expect($result[1]->getLabel())->toBeString()->not->toBeEmpty();
});

it('dehydrateStateUsing in split mode extracts the from-date', function () {
    // dehydrateState() requires a mounted container; we invoke the stored closure directly.
    $picker = DateRangePicker::forColumns('start_date', 'end_date')[1];

    $callback = (fn () => $this->dehydrateStateUsing)->call($picker);

    expect($callback(['from' => '2024-01-01', 'to' => '2024-12-31']))->toBe('2024-01-01');
    expect($callback(['from' => null, 'to' => '2024-12-31']))->toBeNull();
    expect($callback(null))->toBeNull();
});

it('dehydrateStateUsing in single-column mode passes the array through', function () {
    $component = DateRangePicker::make('date_range');

    $callback = (fn () => $this->dehydrateStateUsing)->call($component);

    $state = ['from' => '2024-01-01', 'to' => '2024-12-31'];

    expect($callback($state))->toBe(['from' => '2024-01-01', 'to' => '2024-12-31']);
});
