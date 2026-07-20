# Changelog

All notable changes to `:package_name` will be documented in this file.

## 1.0.0 - 202X-XX-XX

- initial release

## [Unreleased]

## [0.1.5] - 2026-07-20

### Fixed
- **`payment-timeline` group headers (and any descendant control) were unclickable in `scroll`/`combined` mode.** The drag-to-scroll viewport called `setPointerCapture` on every `pointerdown`, which retargets the trailing `pointerup`/`click` to the viewport itself — so the browser dispatched the `click` on the scroll container (the common ancestor) and the collapsible month-header `<button>` inside it never received it. Toggling looked completely dead with no console error. Pointer capture is now **deferred until an actual drag** (movement past a 3px threshold); a plain click reaches its target and toggles the group, while drag-to-scroll still captures once a drag begins and releases on pointer-up. Regression-guarded in `PaymentTimelineTest`.

### Added
- **`payment-timeline` height modes are now first-class.** The `height` prop for `scroll`/`combined` accepts:
  - **a bare number of px** (`height="520"`) in addition to any CSS length (`'34rem'`) — a fixed-height viewport that scrolls internally while the card chrome stays put;
  - **`'auto'`** — drops the viewport bound entirely; the list grows with its content and never scrolls internally (the page scrolls). No scroll container, drag, or edge fades are rendered.

  `'fill'` is unchanged but its contract is now documented explicitly: it **only** bounds when a taller ancestor imposes a height (a flex-column card stretched by a taller grid sibling). With no such bound it resolves to content height and grows unbounded — prefer a fixed `height` (or `'auto'`) for standalone cards. README updated with all three behaviors and examples; `PaymentTimelineTest` covers px-normalization and `auto`.

## [0.1.4] - 2026-07-16

### Added
- **`payment-timeline` fill height.** The `height` prop for `scroll`/`combined` now also accepts `'fill'` (or `true`): the viewport drops its fixed pixel height and stretches to the parent via `flex-1 min-h-0` (the component root becomes a flex column too). Fixed CSS lengths keep working and the default (`24.75rem`) is unchanged. Use inside a flex-column card so the list fills the card and only scrolls on overflow — fixes dead space below the list when a card is stretched by a taller grid sibling. Edge-fade masks and drag/momentum scrolling stay anchored to the viewport and behave identically. README + `PaymentTimelineTest` updated.
- **`payment-timeline` modes.** New `mode` prop — `list` (default, unchanged original behavior and item shape), `scroll` (fixed-height viewport with hidden scrollbar, wheel/touch + pointer-drag scrolling with momentum, edge fades), `grouped` (collapsible month sections with counts and animated chevrons; `late: true` items pin into an expanded "Late" group on top; newest month open), and `combined` (grouped inside the scroll viewport). New props: `height` (scroll viewport, default `24.75rem`) and `lateLabel`. New optional item keys: `date_iso` (sortable `Y-m-d`, required by grouped/combined) and `late` (bool). Grouped modes degrade gracefully (`grouped`→`list`, `combined`→`scroll` + logged warning) when `date_iso` is missing, so pre-existing consumers are untouched. Alpine-only interactivity; light + dark + RTL. Backed by 9 Pest render tests (`PaymentTimelineTest`); README section with the full prop/item reference.

## [0.1.1] - 2026-06-25

### Added
- **`<x-project-essentials::locale-switcher>`** — standard language-switcher dropdown that renders identically inside a Filament panel and on any public Blade page. Ships its own scoped CSS (no `flag-icons` library, no host-Tailwind dependency), uses a globe glyph + native language names, and respects dark mode + RTL. Auto-resolves locales from `codenzia/filament-panel-base`'s `SetLocale::getLocales()`, `config('app.available_locales')`, or the app locale. Backed by `Codenzia\ProjectEssentials\View\Components\LocaleSwitcher` + 10 Pest tests (`LocaleSwitcherTest`). README updated with full prop reference and usage examples.
- `CardRepeater::tableHeader(string|Closure $view)` — render a Blade view once above the card grid instead of putting `@if($isFirst)` headers inside each cardSchema. Keeps all card wrappers the same height so absolute-positioned controls (e.g. the delete button) land at the same spot on every card.
- `DateRangePicker::forColumns(string $fromColumn, string $toColumn, ?string $label = null, bool $required = false): array` — split-column factory that returns the picker bound to two separate DB columns (e.g. `start_date` / `end_date`) instead of a single range value. Used by the Project/Phase/Sprint forms.

### Fixed
- **CardRepeater delete button vanished mid-reach.** The button was positioned at `top-1 -right-10` (40px outside the wrapper) but hover state was tracked on the wrapper, so the cursor crossed out of the parent on its way to the button, fired `mouseleave`, and hid the button before it could be clicked. Anchored the button to `top-1 right-1` inside the wrapper. Card schemas that need horizontal breathing room for the button should reserve it (e.g. `pr-10` on the row content).
- Code-audit findings: authorization gates, state-bleed between component instances, log integrity, and assorted correctness bugs.

## [0.1.0] - 2026-05-20

### Added
- First tracked release. Early beta. Earlier history not recorded in this changelog — see git log for changes prior to release-tracker adoption.
