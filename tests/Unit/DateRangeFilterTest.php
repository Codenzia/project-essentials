<?php

use Codenzia\ProjectEssentials\Models\ActivityLog;
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

it('filters with half-open boundaries so an indexed timestamp column stays usable', function () {
    $filter = DateRangeFilter::make('created_at');

    $callback = (fn () => $this->modifyQueryUsing)->call($filter);

    $query = $callback(
        ActivityLog::query(),
        ['created_at' => ['from' => '2026-01-01', 'to' => '2026-01-31']],
    );

    $bindings = array_map(
        fn ($binding) => $binding instanceof DateTimeInterface ? $binding->format('Y-m-d H:i:s') : $binding,
        $query->getBindings(),
    );

    expect($query->toSql())->not->toContain('strftime')
        ->and($bindings)->toBe(['2026-01-01 00:00:00', '2026-02-01 00:00:00']);
});
