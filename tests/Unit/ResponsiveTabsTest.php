<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

/**
 * The tab ids, the default tab, the persist key and the wire:model path are all
 * interpolated into Alpine expressions. Blade's HTML escaping does not protect a
 * JavaScript string literal — the browser decodes entities before Alpine evaluates
 * the expression — so every one of them must go through a JS-safe encoder.
 */
function renderResponsiveTabs(array $tabs, array $attributes = []): string
{
    $rendered = collect($attributes)
        ->map(fn (string $value, string $key): string => $key . '="' . $value . '"')
        ->implode(' ');

    return Blade::render(
        '<x-project-essentials::responsive-tabs :tabs="$tabs" ' . $rendered . ' />',
        ['tabs' => $tabs],
    );
}

it('keeps a quote in a tab id inert instead of closing the Alpine string literal', function () {
    $html = renderResponsiveTabs([
        ['id' => "a' + fixtureMarker + '", 'label' => 'Hostile'],
        ['id' => 'plain', 'label' => 'Plain'],
    ]);

    expect($html)->not->toContain("activeTab === 'a' + fixtureMarker + ''")
        ->and($html)->not->toContain("activeTab !== 'a' + fixtureMarker + ''")
        ->and($html)->toContain('\\u0027')
        ->and($html)->toContain("activeTab === 'plain'");
});

it('keeps a quote in a tab id out of the serialized tabs array', function () {
    $html = renderResponsiveTabs([
        ['id' => '"><script>fixtureMarker()</script>', 'label' => 'Hostile'],
    ]);

    expect($html)->not->toContain('<script>fixtureMarker()</script>')
        ->and($html)->toContain('responsiveTabs({');
});

it('encodes the persist key and the default tab as JavaScript values', function () {
    $html = renderResponsiveTabs(
        [['id' => "it's", 'label' => 'Apostrophe']],
        ['persist' => 'boards', 'active' => "it's"],
    );

    expect($html)->toContain("persistKey: 'boards'")
        ->and($html)->not->toContain("activeTabInit: 'it's'");
});

it('still selects a tab by its index', function () {
    $html = renderResponsiveTabs([
        ['id' => 'first', 'label' => 'First'],
        ['id' => 'second', 'label' => 'Second'],
    ]);

    expect($html)->toContain('select(1)')
        ->and($html)->toContain('isVisible(1)');
});
