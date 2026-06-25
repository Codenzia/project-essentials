<?php

use Codenzia\ProjectEssentials\Helpers\DateRangeHelper;

it('parses Y-m-d dash ranges with padded separator', function () {
    config()->set('app.date_format', 'Y-m-d');

    $result = DateRangeHelper::parse('2026-01-01 - 2026-02-01');

    expect($result['start_date'])->toBe('2026-01-01');
    expect($result['end_date'])->toBe('2026-02-01');
});

it('parses slash ranges without surrounding spaces via fallback', function () {
    config()->set('app.date_format', 'd/m/Y');

    $result = DateRangeHelper::parse('01/02/2026-03/04/2026');

    expect($result['start_date'])->toBe('01/02/2026');
    expect($result['end_date'])->toBe('03/04/2026');
});

it('parses a single date when end date is optional', function () {
    config()->set('app.date_format', 'Y-m-d');

    $result = DateRangeHelper::parse('2026-01-01', '-', true);

    expect($result['start_date'])->toBe('2026-01-01');
    expect($result['end_date'])->toBeNull();
});

it('tryExplode splits Y-m-d dash ranges correctly', function () {
    $result = DateRangeHelper::tryExplode('2026-01-01 - 2026-02-01');

    expect($result['start_date'])->toBe('2026-01-01');
    expect($result['end_date'])->toBe('2026-02-01');
});
