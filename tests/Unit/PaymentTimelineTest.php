<?php

use Illuminate\Support\Facades\Blade;

function timelineItems(): array
{
    return [
        ['date' => '11 Jul 2026', 'date_iso' => '2026-07-11', 'title' => '550 JOD paid — Apt 4B', 'badge' => 'Paid', 'color' => 'success'],
        ['date' => '5 Jul 2026', 'date_iso' => '2026-07-05', 'title' => '520 JOD — 6 days late', 'badge' => 'Late', 'color' => 'warning', 'late' => true],
        ['date' => '11 Jun 2026', 'date_iso' => '2026-06-11', 'title' => '550 JOD paid — Apt 4B', 'badge' => 'Paid', 'color' => 'success'],
        ['date' => '5 May 2026', 'date_iso' => '2026-05-05', 'title' => '1200 JOD paid — Office 12', 'badge' => 'Paid', 'color' => 'success'],
    ];
}

function renderTimeline(array $attributes = [], ?array $items = null): string
{
    return Blade::render(
        '<x-project-essentials::payment-timeline :items="$items" :empty="$empty" :mode="$mode" :height="$height" />',
        [
            'items' => $items ?? timelineItems(),
            'empty' => $attributes['empty'] ?? 'No payments yet',
            'mode' => $attributes['mode'] ?? 'list',
            'height' => $attributes['height'] ?? '24.75rem',
        ],
    );
}

it('stretches the viewport with flex-1 instead of a fixed height when height is "fill"', function () {
    $html = renderTimeline(['mode' => 'combined', 'height' => 'fill']);

    expect($html)->toContain('class="pe-payment-timeline-scroll min-h-0 flex-1"') // stretches to parent
        ->and($html)->not->toContain('height: fill')                              // no bogus fixed height
        ->and($html)->not->toContain('height: 24.75rem')                          // no fixed height at all
        ->and($html)->toContain('grid-template-rows')                             // groups still render (combined)
        ->and($html)->toContain('mask-image')                                     // edge fades still anchored
        ->and($html)->toContain('setPointerCapture');                             // drag-to-scroll still wired
});

it('keeps a fixed pixel height for scroll/combined when height is a CSS length', function () {
    $html = renderTimeline(['mode' => 'scroll', 'height' => '20rem']);

    expect($html)->toContain('height: 20rem')
        ->and($html)->toContain('class="pe-payment-timeline-scroll"')             // plain viewport class
        ->and($html)->not->toContain('pe-payment-timeline-scroll min-h-0 flex-1'); // no fill classes on it
});

it('renders the default list mode with the ORIGINAL item shape (no date_iso, no late)', function () {
    $html = renderTimeline(items: [
        ['date' => '11 Aug 2026', 'title' => '520 JOD paid — Flat', 'badge' => 'Confirmed', 'color' => 'success'],
        ['date' => '5 Aug 2026', 'title' => '900 JOD paid — Office', 'badge' => 'Confirmed', 'color' => 'success'],
    ]);

    expect($html)->toContain('520 JOD paid — Flat')
        ->and($html)->toContain('bg-emerald-500')
        ->and($html)->not->toContain('class="pe-payment-timeline-scroll"') // no viewport in list mode
        ->and($html)->not->toContain('grid-template-rows');       // no groups in list mode
});

it('renders items in the given order in list mode (no re-sorting)', function () {
    $html = renderTimeline(['mode' => 'list']);

    expect(strpos($html, '550 JOD paid — Apt 4B'))->toBeLessThan(strpos($html, '6 days late'));
});

it('renders a fixed-height hidden-scrollbar viewport in scroll mode', function () {
    $html = renderTimeline(['mode' => 'scroll', 'height' => '20rem']);

    expect($html)->toContain('class="pe-payment-timeline-scroll"')
        ->and($html)->toContain('height: 20rem')
        ->and($html)->toContain('scrollbar-width: none')
        ->and($html)->toContain('mask-image')
        ->and($html)->toContain('setPointerCapture'); // drag-to-scroll wiring
});

it('groups by month with a pinned Late section first in grouped mode', function () {
    $html = renderTimeline(['mode' => 'grouped']);

    $late = strpos($html, 'Late');
    $july = strpos($html, 'July 2026');
    $june = strpos($html, 'June 2026');
    $may = strpos($html, 'May 2026');

    expect($late)->not->toBeFalse()
        ->and($july)->not->toBeFalse()
        ->and($late)->toBeLessThan($july)      // Late pinned on top
        ->and($july)->toBeLessThan($june)      // then months newest-first
        ->and($june)->toBeLessThan($may)
        ->and($html)->toContain('(1)')          // section counts
        ->and($html)->toContain('grid-template-rows');
});

it('falls back to list when grouped items are missing date_iso', function () {
    $html = renderTimeline(['mode' => 'grouped'], [
        ['date' => '11 Aug 2026', 'title' => 'Old-shape row', 'badge' => 'Paid', 'color' => 'success'],
    ]);

    expect($html)->toContain('Old-shape row')
        ->and($html)->not->toContain('grid-template-rows')
        ->and($html)->not->toContain('class="pe-payment-timeline-scroll"');
});

it('falls back to scroll when combined items are missing date_iso', function () {
    $html = renderTimeline(['mode' => 'combined'], [
        ['date' => '11 Aug 2026', 'title' => 'Old-shape row', 'badge' => 'Paid', 'color' => 'success'],
    ]);

    expect($html)->toContain('Old-shape row')
        ->and($html)->toContain('class="pe-payment-timeline-scroll"')
        ->and($html)->not->toContain('grid-template-rows');
});

it('renders groups inside the scroll viewport in combined mode', function () {
    $html = renderTimeline(['mode' => 'combined']);

    expect($html)->toContain('class="pe-payment-timeline-scroll"')
        ->and($html)->toContain('grid-template-rows')
        ->and(strpos($html, 'class="pe-payment-timeline-scroll"'))->toBeLessThan(strpos($html, 'July 2026'));
});

it('shows the empty message when there are no items', function () {
    $html = renderTimeline(['mode' => 'combined', 'empty' => 'Nothing here'], []);

    expect($html)->toContain('Nothing here')
        ->and($html)->not->toContain('class="pe-payment-timeline-scroll"');
});

it('treats an unknown mode as list', function () {
    $html = renderTimeline(['mode' => 'bogus']);

    expect($html)->toContain('550 JOD paid — Apt 4B')
        ->and($html)->not->toContain('class="pe-payment-timeline-scroll"');
});
