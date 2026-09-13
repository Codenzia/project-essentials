<?php

declare(strict_types=1);

use Codenzia\ProjectEssentials\Models\PageSetting;
use Codenzia\ProjectEssentials\Models\PageSettingPreset;
use Codenzia\ProjectEssentials\Tests\Fixtures\PageSettingsHost;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->app['config']->set('session.driver', 'array');
});

it('keeps presets of one scope out of another scope', function () {
    $host = new PageSettingsHost;

    PageSettingPreset::create([
        'name' => 'Team A layout',
        'page' => $host->pageKey(),
        'scope' => 'project-1',
        'settings' => ['columns' => 2],
    ]);

    expect(PageSettingPreset::getForPage($host->pageKey(), 'project-1'))->toHaveCount(1)
        ->and(PageSettingPreset::getForPage($host->pageKey(), 'project-2'))->toHaveCount(0)
        ->and(PageSettingPreset::getForPage($host->pageKey()))->toHaveCount(0);
});

it('seeds a first-time user from the default preset, order included', function () {
    $host = new PageSettingsHost;
    $host->defaults = ['columns' => 1, 'compact' => false];

    PageSettingPreset::create([
        'name' => 'Manager view',
        'page' => $host->pageKey(),
        'scope' => '',
        'settings' => ['columns' => 3],
        'order' => ['revenue', 'tasks'],
        'is_default' => true,
    ]);

    expect($host->data())->toBe(['columns' => 3, 'compact' => false])
        ->and($host->order())->toBe(['revenue', 'tasks']);
});

it('prefers the user own stored settings over the default preset', function () {
    $host = new PageSettingsHost;
    $host->defaults = ['columns' => 1];

    PageSettingPreset::create([
        'name' => 'Manager view',
        'page' => $host->pageKey(),
        'scope' => '',
        'settings' => ['columns' => 3],
        'is_default' => true,
    ]);

    PageSetting::persist(0, $host->pageKey(), ['columns' => 5]);

    expect($host->data())->toBe(['columns' => 5]);
});

it('only applies a default preset of the page scope', function () {
    $host = new PageSettingsHost;
    $host->scope = 'project-9';
    $host->defaults = ['columns' => 1];

    PageSettingPreset::create([
        'name' => 'Unscoped default',
        'page' => $host->pageKey(),
        'scope' => '',
        'settings' => ['columns' => 3],
        'is_default' => true,
    ]);

    expect($host->data())->toBe(['columns' => 1]);
});

it('keeps a newly added nested default when older settings store the same top-level key', function () {
    $host = new PageSettingsHost;
    $host->defaults = ['widgets' => ['revenue' => true, 'forecast' => true]];

    PageSetting::persist(0, $host->pageKey(), ['widgets' => ['revenue' => false]]);

    expect($host->data())->toBe(['widgets' => ['revenue' => false, 'forecast' => true]]);
});

it('replaces a stored list instead of merging it item by item', function () {
    $host = new PageSettingsHost;
    $host->defaults = ['columns' => ['name', 'status', 'due']];

    PageSetting::persist(0, $host->pageKey(), ['columns' => ['name']]);

    expect($host->data())->toBe(['columns' => ['name']]);
});

it('carries the preset order across when a preset is applied', function () {
    $host = new PageSettingsHost;

    $preset = PageSettingPreset::create([
        'name' => 'Manager view',
        'page' => $host->pageKey(),
        'scope' => '',
        'settings' => ['columns' => 3],
        'order' => ['tasks', 'revenue'],
    ]);

    $host->applyPreset($preset->id);
    $host->forgetResolved();

    $stored = PageSetting::getForUser(0, $host->pageKey());

    expect($stored->settings)->toBe(['columns' => 3])
        ->and($stored->order)->toBe(['tasks', 'revenue']);
});

it('will not apply a preset belonging to another scope', function () {
    $host = new PageSettingsHost;
    $host->scope = 'project-9';

    $preset = PageSettingPreset::create([
        'name' => 'Other scope',
        'page' => $host->pageKey(),
        'scope' => 'project-1',
        'settings' => ['columns' => 3],
    ]);

    $host->applyPreset($preset->id);

    expect(PageSetting::getForUser(0, $host->pageKey(), 'project-9'))->toBeNull();
});
