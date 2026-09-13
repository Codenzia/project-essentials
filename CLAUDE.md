# CLAUDE.md — project-essentials

Codenzia global standards apply (see `GitHub/CLAUDE.md`). Package-specific policy below.

## BVT policy (Build Verification Tests)

This package is a reusable Filament component library, so `tests/BVT/` is a thin
**construction/existence net** — one shallow check per shipped building block.
Its job is to fail loudly when a form field, infolist entry, table column,
filter, action, view component, or shipped Blade view is renamed, deleted, or
starts fataling at construction. It is **not** a place for behaviour coverage.

Rules when touching this package:

- **Every new component** gets a matching entry in the `tests/BVT/` roll-call:
  Filament fields/entries/columns/filters/actions are asserted to construct via
  `::make()` and be an instance of their Filament base type; view component
  classes are asserted to load.
- **New Blade views are covered automatically** — the view-resolution test
  scans `resources/views/` at runtime.
- **Keep BVT shallow.** Deep behaviour lives alongside it in the surface suites
  (`PaymentTimelineTest`, `StateSwitcherTest`, `CardRepeaterTest`,
  `DateRangeFilterTest`, `FilamentTableHelperTest`, …) — BVT does not replace or
  duplicate them.
- Components that read `app.date_format` / `app.datetime_format` must stay
  null-safe (default internally); a consumer may set one and not the other.
- Update `FEATURES.md` when the surface inventory changes.

## Review docs

`fable-*` files are internal review scratch — gitignored, never commit them.
