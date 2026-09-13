# Changelog

All notable changes to `codenzia/project-essentials` will be documented in this file.

## [0.2.1] - 2026-09-13

### Fixed
- `HasPageSettings` imported `Filament\Forms\Components\Component` for the `@return array<PageSettingDefinition|Component>` contracts on its schema builders. That class was removed in Filament v4, and it contradicted the actual return type of `PageSettingDefinition::toFormComponent()`, which already returns `Filament\Schemas\Components\Component`. The import now names the real class.

### Added
- `tests/BVT/FilamentImportsResolveTest` resolves every `use Filament\...;` import in `src/` against the installed Filament.

## [0.2.0] - 2026-09-08

### Changed — action required

- **`StateSwitcher` now refuses an inline state change until the host registers a guard.** An inline column write bypasses the resource policy — Filament checks only the column's `hidden()`/`disabled()` state before saving — so a view-only user could flip a record by calling the Livewire method directly, and no transition rule was consulted. The column gains `authorizeStateChangeUsing(?Closure $callback)`, receiving `$record`, the `$state` about to be written and the `$column` name; the write happens only when it returns true. **Every existing `StateSwitcher` stops writing until this is set** — see the StateSwitcher section of the README.
- **`applyPageSettingsToTeam()` no longer accepts a team from the caller.** It took `(int $teamId, ?string $teamRelation = null)` — an arbitrary column name and id off the wire behind a single boolean permission check, so an authorized manager could write settings for users outside their own team. The team now comes from the new overridable `getPageSettingsTeamId()` and the column from `config('project-essentials.page_settings.team_column')`; the method is refused (403) while the hook returns `null`. Both bulk-apply methods now select and upsert in chunks of 500 instead of plucking every id and building every row in memory.
- **`DateRangeHelper::parse()` rejects impossible and reversed ranges.** Carbon rolled `31/02/2026` forward into `03/03/2026`, silently producing a start after the requested end; a parse now only succeeds when the value round-trips to its input, and a range whose end precedes its start throws `InvalidArgumentException`. The `Y-m-d` docblock was corrected to describe what the method actually returns (values in `config('app.date_format')`).

### Fixed

- **Tab ids were interpolated into Alpine expressions as raw JavaScript.** `responsive-tabs` emitted `activeTab === '{{ $tab['id'] }}'`, the tabs array through a bare `json_encode`, and the persist key / `wire:model` path as hand-quoted strings. Blade's HTML escaping does not protect a JavaScript string literal — the browser decodes entities before Alpine evaluates the expression — so a quote in an id derived from data broke out of the literal. Every JavaScript value now goes through `Js::from()`/`@js()`. Ordinary Blade escaping is unchanged for labels.
- **The state switcher sent the Livewire component id where a record key belongs, and reported a false success.** `updateTableColumnState()` looks the record up by that argument, so the call resolved no record and returned `null` — which the view treated as success and used to move the switch. The view now passes `$getRecordKey()`, treats a `null`/error response as a failure, and renders the state the server actually persisted. Its `wire:key` also used PHP truthiness on the raw state, so `Active` and `Inactive` produced the same key and a `wire:ignore`d switch could stay stale after an external update; it now keys on the normalized on/off state.
- **Page-setting presets ignored scope, order and their own default flag.** Presets were stored and queried per page only, so every scope of a scoped page shared one pool; applying a preset restored its values but left the previous item order; and `PageSettingPreset::getDefaultForPage()` was never called, so marking a preset default did nothing. Presets now carry a `scope` column resolved by the new overridable `getPageSettingsPresetScope()` (defaults to the page settings scope), applying a preset carries its order, and a user with no stored row for that page + scope is seeded from the default preset.
- **A nested default was dropped by an older stored settings array.** `getPageSettingsData()` merged stored settings over defaults with a shallow `array_merge`, so adding a nested default under an existing top-level key lost every sibling. The merge is now recursive, with lists replaced wholesale rather than merged item by item.
- **A password-only change was not audited at all.** `CanLogsActivity` stripped sensitive keys from the diff and then returned early when nothing was left, so a credential change left no record despite the comment promising one. Redacted keys are now kept with a `[redacted]` value so the event is recorded without the values, and sensitive keys nested inside a permitted array/JSON attribute are scrubbed recursively. A logging failure now records the model, key and exception class instead of the driver message, which can carry the SQL statement and its bound values.
- **An explicitly null `app.date_format` / `app.datetime_format` bypassed the internal fallback.** `config('app.date_format', 'd M, Y')` returns `null` when the key exists and is null, which reached `DateRangePicker::getDateFormat()`'s `string` return type as a `TypeError`. Every date-format read in the package now uses a null-coalescing fallback (`DateRangePicker`, `DatePickerWithHint`, `DateTimePickerWithHint`, `DateRangeFilter`, `DateRangeHelper`).
- **`DateRangeFilter` wrapped the filtered column in a date function.** `whereDate()` on an indexed timestamp column forces a per-row evaluation; the filter now uses half-open boundaries (`>= from 00:00:00`, `< to + 1 day`), which selects the same records and leaves the index usable.
- **`CounterInput` had no server-side bounds and ignored its disabled state.** The rendered input and both +/- buttons stayed enabled and submittable for a disabled field, values alternated between numbers and strings (`state.replace()` fataled on a number, `parseInt(null)` produced `NaN`), and the icon-only buttons had no accessible name. The field now enforces whole-number/min/max validation on the server, honours `disabled()`, wires the input to the field's id, normalizes through `String`/finite-number checks, and labels both steppers.

### Added

- `CanLogsActivity::withoutActivityLogging(callable $callback)` — scoped suppression that restores the previous state in a `finally`, so a suppression cannot leak into the next job on a queue worker. The process-wide `$skipLogging` flag stays for backwards compatibility.
- `CounterInput::minValue()` / `maxValue()` — bounds honoured by both the stepper and server-side validation (`minValue` defaults to `0`).
- `DateRangePicker` now validates on the server that the range ends on or after it starts, in both single-field and split-column (`forColumns()`) mode.
- Migration `add_scope_to_page_setting_presets_table` adds the preset `scope` column to an existing install; fresh installs get it from `create_page_settings_table`. **Existing apps must re-publish migrations and run `php artisan migrate`.**

## [0.1.6] - 2026-07-21

### Fixed
- **`StateSwitcher` column fatal-500'd every table it appeared in.** The `state-switcher` blade echoed the record key as a bare `{{ $recordKey }}`, but under Filament v5 a column view's magic variables are first-class callables extracted from the component's public methods — so `$recordKey` resolved to the `recordKey()` **setter closure**, not the key string. Echoing it hit `htmlspecialchars(): Argument #1 ($string) must be of type string, Closure given` and the whole list page (e.g. Task-Off's `ListUsers`) returned 500. The blade now calls the resolved accessor `{{ $getRecordKey() }}`. The extra-attribute merge was also switched from `$attributes->merge($getExtraAttributes(), …)` to Filament core's `$getExtraAttributeBag()` convention, which resolves extra-attribute closures the same way core columns do. Regression-guarded by a Livewire render test (`StateSwitcherTest`).

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
