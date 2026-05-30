<?php

use Codenzia\ProjectEssentials\Tables\Filters\DateRangeFilter;

it('can be instantiated', function () {
    $filter = DateRangeFilter::make('created_at');

    expect($filter)->toBeInstanceOf(DateRangeFilter::class);
    expect($filter->getName())->toBe('created_at');
});

it('defaults column to its name', function () {
    $filter = DateRangeFilter::make('created_at');

    expect($filter->getColumn())->toBe('created_at');
});

it('allows custom column', function () {
    $filter = DateRangeFilter::make('date_filter')
        ->column('published_at');

    expect($filter->getColumn())->toBe('published_at');
});

it('allows custom placeholder', function () {
    $filter = DateRangeFilter::make('created_at')
        ->placeholder('Filter by date');

    expect($filter->getPlaceholder())->toBe('Filter by date');
});

it('supports fluent chaining', function () {
    $filter = DateRangeFilter::make('date_filter')
        ->column('due_date')
        ->placeholder('Due date range');

    expect($filter->getColumn())->toBe('due_date');
    expect($filter->getPlaceholder())->toBe('Due date range');
});

it('has empty label by default', function () {
    $filter = DateRangeFilter::make('created_at');

    expect($filter->getLabel())->toBe('');
});
