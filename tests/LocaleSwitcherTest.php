<?php

use Codenzia\ProjectEssentials\View\Components\LocaleSwitcher;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    // The component links to a named switch route; register a stub.
    Route::get('/locale/{locale}', fn () => '')->name('locale.switch');
});

it('normalizes native names + direction from bare locale codes', function () {
    $c = new LocaleSwitcher(locales: ['en' => [], 'ar' => []]);

    expect($c->locales['en']['native'])->toBe('English');
    expect($c->locales['en']['dir'])->toBe('ltr');
    expect($c->locales['ar']['native'])->toBe('العربية');
    expect($c->locales['ar']['dir'])->toBe('rtl');
});

it('accepts list-style locale arrays', function () {
    $c = new LocaleSwitcher(locales: ['en', 'fr']);

    expect(array_keys($c->locales))->toBe(['en', 'fr']);
    expect($c->locales['fr']['native'])->toBe('Français');
});

it('prefers a caller-supplied native name over the built-in map', function () {
    $c = new LocaleSwitcher(locales: ['en' => ['native' => 'British English']]);

    expect($c->locales['en']['native'])->toBe('British English');
});

it('falls back to the uppercased code for unknown locales', function () {
    $c = new LocaleSwitcher(locales: ['en' => [], 'xx' => []]);

    expect($c->locales['xx']['native'])->toBe('XX');
});

it('does not render when only one locale is available', function () {
    $c = new LocaleSwitcher(locales: ['en' => []]);

    expect($c->shouldRender())->toBeFalse();
});

it('renders when more than one locale is available', function () {
    $c = new LocaleSwitcher(locales: ['en' => [], 'ar' => []]);

    expect($c->shouldRender())->toBeTrue();
});

it('renders self-contained markup without flag-icons or tailwind utilities', function () {
    $html = Blade::render(
        '<x-project-essentials::locale-switcher :locales="$locales" current-locale="en" />',
        ['locales' => ['en' => [], 'ar' => []]],
    );

    // Scoped, prefixed classes (not Tailwind utilities, not flag sprites).
    expect($html)
        ->toContain('pe-ls__trigger')
        ->toContain('pe-ls__menu')
        ->not->toContain('flag flag-') // no flag-icons dependency
        ->toContain('English')
        ->toContain('العربية');
});

it('marks the active locale with aria-current', function () {
    $html = Blade::render(
        '<x-project-essentials::locale-switcher :locales="$locales" current-locale="ar" />',
        ['locales' => ['en' => [], 'ar' => []]],
    );

    expect($html)->toMatch('/lang="ar"[^>]*aria-current="true"/');
});

it('links each locale to the configured switch route', function () {
    $html = Blade::render(
        '<x-project-essentials::locale-switcher :locales="$locales" />',
        ['locales' => ['en' => [], 'ar' => []]],
    );

    expect($html)
        ->toContain('/locale/en')
        ->toContain('/locale/ar');
});

it('honors the align prop', function () {
    $start = Blade::render('<x-project-essentials::locale-switcher :locales="$l" align="start" />', ['l' => ['en' => [], 'ar' => []]]);
    $end = Blade::render('<x-project-essentials::locale-switcher :locales="$l" align="end" />', ['l' => ['en' => [], 'ar' => []]]);

    expect($start)->toContain('pe-ls__menu--start');
    expect($end)->toContain('pe-ls__menu--end');
});
