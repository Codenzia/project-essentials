# Changelog

All notable changes to `:package_name` will be documented in this file.

## 1.0.0 - 202X-XX-XX

- initial release

## [Unreleased]

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
