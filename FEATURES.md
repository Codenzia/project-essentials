# project-essentials — Feature Surfaces

Inventory of the reusable Filament building blocks this package ships and where
each is covered. **BVT** points at the thin existence/construction net under
`tests/BVT/`; **Deep** points at the surface-specific behaviour suites.

## Form components (`Forms\Components`)

`CardRepeater`, `CounterInput`, `DatePickerWithHint`, `DateRangePicker`,
`DateTimePickerWithHint`, `IconColoredEnumSelect`, `IconPicker`,
`PercentageSlider`, `UserSelectWithAvatar` — each constructs via `::make()` in
the BVT net. Deep: `CardRepeaterTest`, `CounterInputTest`,
`DateRangePickerTest`, `DateRangeHelperTest`.

## Infolist entries (`Infolists\Components`)

`ColoredEnumEntry`, `ColoredPillsEntry`, `CreatedUpdatedEntry`, `PriorityEntry`,
`ProgressBarEntry`, `TagEntry`, `UserAvatarEntry` — construction roll-call.

## Table columns (`Tables\Columns`)

`CircularProgressBar`, `ColoredEnumColumn`, `ColoredPillsColumn`,
`CreatedUpdatedColumn`, `HtmlColumn`, `PriorityColumn`, `ProgressBarColumn`,
`StateSwitcher`, `TagColumn`, `UserAvatarColumn` — construction roll-call. Deep:
`StateSwitcherTest`, `CanToggleColumnsTest`, `FilamentTableHelperTest`.

## Filters & actions

`Tables\Filters\DateRangeFilter` (deep: `DateRangeFilterTest`),
`Filament\Actions\SplitButtonDropdownAction` — construction roll-call.

## Blade view components & views

`View\Components\{LoadingSpinner,LocaleSwitcher,Progress}` — class roll-call.
Every packaged Blade view under `resources/views/` (including the
`payment-timeline`, `contract-stepper`, `state-switcher`, `circular-progress-bar`
templates) is covered by the runtime view-resolution scan. Deep:
`PaymentTimelineTest`, `LocaleSwitcherTest`, `ResponsiveTabsTest`.

## Traits

`HasPageSettings` (deep: `HasPageSettingsTest`, `PageSettingTest`),
`CanLogsActivity` (deep: `CanLogsActivityTest`), `CanToggleColumns` (deep:
`CanToggleColumnsTest`).

## BVT scope

The BVT layer (`tests/BVT/`) is the thin **build-verification** net for this
component library: every shipped Filament field/entry/column/filter/action must
construct via `::make()` without a fatal, every view component class must load,
and every shipped Blade view must resolve (scanned at runtime). It sits
**alongside** the deep surface suites — it does not replace them and must not
duplicate their depth.
