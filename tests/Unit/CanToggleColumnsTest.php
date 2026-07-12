<?php

use Codenzia\ProjectEssentials\Tests\Fixtures\ToggleColumnsHost;
use Filament\Tables\Columns\TextColumn;

beforeEach(function () {
    $this->app['config']->set('session.driver', 'array');
});

it('round-trips toggle state through the session key', function () {
    $host = new ToggleColumnsHost;

    $columns = [
        TextColumn::make('name')->toggleable(),
        TextColumn::make('email')->toggleable(),
    ];

    $host->makeColumnsForToggle($columns);

    expect($host->isColumnVisible('name'))->toBeTrue();

    $host->toggleColumnVisibility('name');

    expect($host->isColumnVisible('name'))->toBeFalse()
        ->and(session()->get($host->getTableToggleStateFromSessionKey())['name'])->toBeFalse();

    // A fresh host reading the same session key sees the persisted state.
    $host2 = new ToggleColumnsHost;
    $host2->makeColumnsForToggle($columns);

    expect($host2->isColumnVisible('name'))->toBeFalse();
});

it('toggles all columns on and off', function () {
    $host = new ToggleColumnsHost;

    $columns = [
        TextColumn::make('name')->toggleable(),
        TextColumn::make('email')->toggleable(),
    ];

    $host->makeColumnsForToggle($columns);
    $host->toggleAllColumns();

    // toggleAllColumns() persists to the session; a re-render (fresh host) picks it up.
    $host = new ToggleColumnsHost;
    $host->makeColumnsForToggle($columns);

    expect($host->isColumnVisible('name'))->toBeFalse();
    expect($host->isColumnVisible('email'))->toBeFalse();

    $host->toggleAllColumns();

    $host = new ToggleColumnsHost;
    $host->makeColumnsForToggle($columns);

    expect($host->isColumnVisible('name'))->toBeTrue();
    expect($host->isColumnVisible('email'))->toBeTrue();
});
