<?php

use Codenzia\ProjectEssentials\Forms\Components\DateRangePicker;

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
