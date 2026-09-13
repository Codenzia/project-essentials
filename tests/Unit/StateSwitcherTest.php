<?php

declare(strict_types=1);

use Codenzia\ProjectEssentials\Tests\Fixtures\StateSwitcherRecord;
use Codenzia\ProjectEssentials\Tests\Fixtures\StateSwitcherTable;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

beforeEach(function () {
    $this->app['config']->set('session.driver', 'array');

    Schema::create('state_switcher_records', function ($table) {
        $table->increments('id');
        $table->string('status')->default('Inactive');
    });

    StateSwitcherRecord::create(['status' => 'Active']);
    StateSwitcherRecord::create(['status' => 'Inactive']);
});

it('renders the state-switcher column without a Closure htmlspecialchars fatal', function () {
    Livewire::test(StateSwitcherTable::class)
        ->assertOk()
        ->assertSee('fi-ta-toggle', escape: false);
});

it('refuses an inline state change while the host configured no authorization hook', function () {
    $record = StateSwitcherRecord::query()->where('status', 'Inactive')->firstOrFail();

    Livewire::test(StateSwitcherTable::class)
        ->call('updateTableColumnState', 'status', (string) $record->getKey(), true);

    expect($record->fresh()->status)->toBe('Inactive');
});

it('updates the clicked record when the host authorizes the change', function () {
    $record = StateSwitcherRecord::query()->where('status', 'Inactive')->firstOrFail();
    $other = StateSwitcherRecord::query()->where('status', 'Active')->firstOrFail();

    Livewire::test(StateSwitcherTable::class, ['authorizeChanges' => true])
        ->call('updateTableColumnState', 'status', (string) $record->getKey(), true);

    expect($record->fresh()->status)->toBe('Active')
        ->and($other->fresh()->status)->toBe('Active');
});

it('changes nothing when the argument is a component id instead of a record key', function () {
    $component = Livewire::test(StateSwitcherTable::class, ['authorizeChanges' => true]);

    $component->call('updateTableColumnState', 'status', $component->id(), true);

    expect(StateSwitcherRecord::query()->where('status', 'Active')->count())->toBe(1);
});

it('reports the persisted state back to the switch', function () {
    $record = StateSwitcherRecord::query()->where('status', 'Inactive')->firstOrFail();

    Livewire::test(StateSwitcherTable::class, ['authorizeChanges' => true])
        ->call('updateTableColumnState', 'status', (string) $record->getKey(), true)
        ->assertReturned(['state' => true]);
});

it('sends the record key to the column state update, not the component id', function () {
    $record = StateSwitcherRecord::query()->firstOrFail();

    $component = Livewire::test(StateSwitcherTable::class);
    $compact = preg_replace('/\s+/', '', $component->html());

    expect($compact)->toContain("updateTableColumnState('status','" . $record->getKey() . "',")
        ->and($compact)->not->toContain("updateTableColumnState('status','" . $component->id() . "'");
});
