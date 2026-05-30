# Project Essentials — UI components, form fields, table columns & helpers for Filament

[![Latest Version](https://img.shields.io/packagist/v/codenzia/project-essentials.svg?style=flat-square)](https://packagist.org/packages/codenzia/project-essentials)
[![PHP Version](https://img.shields.io/packagist/php-v/codenzia/project-essentials.svg?style=flat-square)](https://packagist.org/packages/codenzia/project-essentials)
[![Filament](https://img.shields.io/badge/Filament-v4%20%7C%20v5-f59e0b?style=flat-square)](https://filamentphp.com)
[![License](https://img.shields.io/packagist/l/codenzia/project-essentials.svg?style=flat-square)](LICENSE.md)

**Essential UI components, form fields, table columns, infolist entries, traits, and helpers for [Laravel](https://laravel.com) and [Filament v4 / v5](https://filamentphp.com) projects.** A curated component library distilled from every Codenzia Filament app — the bits we reach for in every project that aren't worth re-implementing each time.

> **Why this exists.** Every Filament CRUD project eventually needs a progress column, a coloured-pill list, an icon picker, an RTL-aware paginator, a tag column with overflow handling, a percentage slider form field. Building these one project at a time is wasted hours; bundling them into a versioned package keeps the API consistent across every app you ship.

> **Try it live:** A working integration is included in the [Codenzia plugins demo](https://github.com/Codenzia/plugins-demo) at `/admin/demo/project-essentials`.

---

## Requirements

| Dependency | Version |
|---|---|
| PHP | `^8.3` |
| Filament | `^4.0 \|\| ^5.0` |

---

## Features

**UI Components**
- **Progress** — Circular SVG progress indicator with gradient colors
- **Pagination** — Custom Laravel pagination view with RTL and dark mode support
- **IconPicker** — Filament form select with 45+ categorized Heroicons
- **DropdownCheckList** — Multi-select dropdown with search, avatars, and enum support
- **DropdownLink** — Styled dropdown link item
- **SplitButtonDropdown** — Split button with main action + dropdown menu
- **PercentageSlider** — Interactive 0-100% slider form field
- **CounterInput** — Increment/decrement number input field
- **ProgressBarColumn / ProgressBarEntry** — Progress bar for tables and infolists
- **ColoredEnumColumn / ColoredEnumEntry** — Enum-aware colored columns and entries
- **HtmlColumn** — Render raw HTML content in table columns
- **UserAvatarColumn / UserAvatarEntry** — User avatar display with fallback
- **TagColumn / TagEntry** — Tag display with color randomization and context limits
- **ColoredPillsColumn / ColoredPillsEntry** — Generic colored pill badges with per-item colors, sizes, enum support, and hover card overflow
- **PriorityColumn / PriorityEntry** — Priority display with icon, color, and label from enums
- **CircularProgressBar** — SVG circular progress bar with gradient
- **CreatedUpdatedColumn / CreatedUpdatedEntry** — Created/updated timestamps with user avatars
- **StateSwitcher** — Toggle column with configurable on/off enum states and labels
- **ResponsiveTabs** — Responsive tabs with overflow "More" dropdown
- **LoadingSpinner** — Advanced loading spinner with 4 variants (minimal, elegant, orbital, pulse)
- **Banner** — Top-of-page notification banner with success/danger/warning styles
- **GridLayoutSwitcher** — Session-persistent grid size slider
- **ColumnToggle** — Custom column visibility toggling with session persistence
- **LocaleSwitcher** — Self-contained language switcher dropdown (works in Filament panels AND public Blade pages; no flag-icons or host-Tailwind dependency)

**Form Components**
- **IconColoredEnumSelect** — Rich select with colored icons for enums
- **DatePickerWithHint** — DatePicker with auto format hint from config
- **DateTimePickerWithHint** — DateTimePicker with auto format hint from config
- **CounterInput** — Stepper number input with +/- buttons
- **DateRangePicker** — Date range picker with from/to inputs, clear button, and locale-aware display
- **CardRepeater** — Card-based repeater with inline/card modes, relationship support, and CRUD actions

**Table Filters**
- **DateRangeFilter** — Table filter using DateRangePicker with automatic query scoping and indicators

**Helpers**
- **DateHelper** — Date formatting, duration, and human-readable format hints
- **DateRangeHelper** — Parse and format date ranges
- **FileHelper** — File type detection by extension
- **TailwindHelper** — Tailwind v4 safe dynamic color class resolver
- **FormatHelper** — Text truncation, hex-to-rgba, formatted IDs
- **FilamentTableHelper** — Reusable timestamp/userstamp columns and search filters

**Traits**
- **IconColoredEnum** — Labels, icons, and colors for PHP enums
- **Userstamps** — Auto-track created_by / updated_by on models
- **CanToggleColumns** — Session-persistent table column toggling
- **CanFixFilamentActionCancel** — Fix for Filament modal cancel bug
- **CanLogsActivity** — Automatic activity logging with elegant diffing
- **HasGridLayoutSwitcher** — Grid layout size with session persistence
- **HasDashboardDateFilter** — Dashboard widget date filtering
- **HasHeaderFormActions** — Mirror form footer actions to page header
- **HasPageSettings** — Database-backed per-user page settings with presets, grouping, reordering, live preview, and admin bulk-apply
- **HasColoredEnumViewComponent** — Enum coloring for columns/entries
- **HasProgressBarViewComponent** — Configurable progress bar rendering
- **HasUserAvatarViewComponent** — Size configuration for avatar columns/entries
- **HasCreatedUpdatedViewComponent** — Username limit configuration for created/updated columns/entries

## Requirements

- PHP 8.3+
- Laravel 12+
- Filament 4.x

## Installation

```bash
composer require codenzia/project-essentials
```

Publish the config (optional):

```bash
php artisan vendor:publish --tag="project-essentials-config"
```

Publish and run migrations (required for HasPageSettings):

```bash
php artisan vendor:publish --tag="project-essentials-migrations"
php artisan migrate
```

Publish views for customization (optional):

```bash
php artisan vendor:publish --tag="project-essentials-views"
```

## Plugin Registration

Register the plugin in your Filament panel provider:

```php
use Codenzia\ProjectEssentials\ProjectEssentialsPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            ProjectEssentialsPlugin::make(),
        ]);
}
```

---

## Traits

### IconColoredEnum

Gives PHP backed enums labels, icons, and Tailwind-compatible colors.

```php
use Codenzia\ProjectEssentials\Traits\IconColoredEnum;

enum StatusEnum: string
{
    use IconColoredEnum;

    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    public static function getLabels(): array
    {
        return [
            self::ACTIVE->value => 'Active',
            self::INACTIVE->value => 'Inactive',
        ];
    }

    public static function getIcons(): array
    {
        return [
            self::ACTIVE->value => 'heroicon-o-check-circle',
            self::INACTIVE->value => 'heroicon-o-x-circle',
        ];
    }

    public static function getColors(): array
    {
        return [
            self::ACTIVE->value => 'success',
            self::INACTIVE->value => 'danger',
        ];
    }

}

// Usage
$status = StatusEnum::ACTIVE;
$status->getLabel();           // "Active"
$status->getIcon();            // "heroicon-o-check-circle"
$status->getColor();           // "success"
$status->tailwindColorClass(); // "text-success-500"

StatusEnum::labels();          // ['active' => 'Active', 'inactive' => 'Inactive']
StatusEnum::options();         // [['value' => 'active', 'label' => 'Active'], ...]
StatusEnum::casesExcept([StatusEnum::INACTIVE]); // [StatusEnum::ACTIVE]
```

### Userstamps

Automatically tracks `created_by_user_id` and `updated_by_user_id` on Eloquent models.

```php
use Codenzia\ProjectEssentials\Traits\Userstamps;

class Invoice extends Model
{
    use Userstamps;
}

// Requires migration columns:
// $table->foreignId('created_by_user_id')->nullable();
// $table->foreignId('updated_by_user_id')->nullable();

$invoice->createdByUser; // User who created
$invoice->updatedByUser; // User who last updated
```

Configure the User model in `config/project-essentials.php`:

```php
'user_model' => 'App\\Models\\User',
```

### CanToggleColumns

Session-persistent column visibility toggling for Filament tables. Replaces Filament's built-in toggle with a custom dropdown.

```php
use Codenzia\ProjectEssentials\Traits\CanToggleColumns;

class ListUsers extends ListRecords
{
    use CanToggleColumns;

    public function table(Table $table): Table
    {
        $columns = [
            TextColumn::make('name'),
            TextColumn::make('email')->toggleable(),
            TextColumn::make('phone')->toggleable(isToggledHiddenByDefault: true),
        ];

        return $table->columns(
            $this->makeColumnsForToggle($columns)
        );
    }
}
```

### CanFixFilamentActionCancel

Fixes a bug in Filament where `modalCancelAction` callbacks execute on modal render instead of button click.

```php
use Codenzia\ProjectEssentials\Traits\CanFixFilamentActionCancel;

class ViewUser extends ViewRecord
{
    use CanFixFilamentActionCancel;
}
```

### HasGridLayoutSwitcher

Provides session-persistent grid layout size switching for Filament pages with card/grid views.

```php
use Codenzia\ProjectEssentials\Traits\HasGridLayoutSwitcher;

class ListProjects extends ListRecords
{
    use HasGridLayoutSwitcher;

    protected function getHeaderActions(): array
    {
        return [
            $this->makeGridLayoutSwitcherAction(),
        ];
    }
}
```

### HasDashboardDateFilter

Adds date range filtering to Filament dashboard widgets.

```php
use Codenzia\ProjectEssentials\Traits\HasDashboardDateFilter;

class RevenueChart extends ChartWidget
{
    use HasDashboardDateFilter;

    protected function getData(): array
    {
        $startDate = $this->getFilterStartDate();
        // Filter your query with $startDate...
    }
}
```

### HasHeaderFormActions

Mirrors form footer actions (Save, Cancel, etc.) to the page header automatically.

```php
use Codenzia\ProjectEssentials\Traits\HasHeaderFormActions;

class EditUser extends EditRecord
{
    use HasHeaderFormActions;
    // Header will now show the same actions as the footer
}
```

### HasPageSettings

Database-backed per-user settings modal for any Filament page or widget. Supports section grouping, admin presets, live preview, reset to defaults, scoped settings, and bulk-apply to roles/teams.

**Requires migration** — run `php artisan vendor:publish --tag="project-essentials-migrations" && php artisan migrate`

#### Basic Usage (raw Filament components)

```php
use Codenzia\ProjectEssentials\Traits\HasPageSettings;

class ListInvoices extends ListRecords
{
    use HasPageSettings;

    protected function getHeaderActions(): array
    {
        return [
            $this->settings([
                Toggle::make('show_archived')->label('Show Archived'),
                Select::make('default_view')->options(['table' => 'Table', 'grid' => 'Grid']),
            ]),
        ];
    }

    // Read a setting value anywhere in the page
    public function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if ($this->getPageSetting('show_archived', false)) {
            $query->withArchived();
        }

        return $query;
    }
}
```

#### Fluent Definitions with PageSettingDefinition

Use the fluent builder for grouped settings with labels, descriptions, and defaults:

```php
use Codenzia\ProjectEssentials\Models\PageSettingDefinition;
use Codenzia\ProjectEssentials\Traits\HasPageSettings;

class Dashboard extends Page
{
    use HasPageSettings;

    protected function getHeaderActions(): array
    {
        return [
            $this->settings([
                // KPI Cards group
                PageSettingDefinition::checkbox('show_revenue')
                    ->label('Revenue')
                    ->description('Monthly revenue breakdown and trends')
                    ->group('KPI Cards')
                    ->default(true),

                PageSettingDefinition::checkbox('show_tasks')
                    ->label('Active Tasks')
                    ->group('KPI Cards')
                    ->default(true),

                // Display group
                PageSettingDefinition::select('default_view')
                    ->label('Default View')
                    ->options(['table' => 'Table', 'grid' => 'Grid'])
                    ->group('Display')
                    ->default('table'),

                PageSettingDefinition::toggle('compact_mode')
                    ->label('Compact Mode')
                    ->description('Reduce spacing between elements')
                    ->group('Display')
                    ->default(false),
            ]),
        ];
    }
}
```

#### Live Preview

Enable reactive updates that apply instantly as the user toggles settings (before submitting):

```php
class Dashboard extends Page
{
    use HasPageSettings;

    protected function pageSettingsLivePreview(): bool
    {
        return true;
    }

    protected function onPageSettingPreviewUpdated(string $key, mixed $value): void
    {
        // Reactively update your page state
        if (str_starts_with($key, 'smartStatsVisibility.')) {
            $this->calculateMetrics();
        }
    }
}
```

#### Scoped Settings

Store different settings per context (e.g., per project):

```php
class ProjectBoard extends Page
{
    use HasPageSettings;

    public Project $project;

    protected function getPageSettingsScope(): ?string
    {
        return (string) $this->project->id;
    }
}
```

#### Admin Presets

Save and load named configurations:

```php
// Save current settings as a preset
$this->saveAsPageSettingsPreset('Manager View', isDefault: true);

// Apply a preset by ID
$this->applyPageSettingsPreset($presetId);

// Delete a preset
$this->deletePageSettingsPreset($presetId);
```

When presets exist for a page, a "Load Preset" dropdown automatically appears at the top of the settings modal.

#### Bulk Apply (Admin)

Push settings to all users with a specific role or in a department:

```php
// Apply to all users with the "manager" role
$this->applyPageSettingsToRole('manager');

// Apply to all users in a department
$this->applyPageSettingsToTeam(teamId: $departmentId, teamRelation: 'department_id');
```

#### Reset to Defaults

A "Reset to Defaults" button is automatically added to the settings modal footer. You can also call it programmatically:

```php
$this->resetPageSettingsToDefaults();
```

#### Default Values

Define sensible defaults that apply before the user ever opens the modal:

```php
protected function getPageSettingsDefaults(): array
{
    return [
        'show_archived' => false,
        'default_view' => 'table',
        'compact_mode' => false,
    ];
}
```

When using `PageSettingDefinition`, defaults are automatically extracted from each definition's `->default()` value.

#### Available Methods

| Method | Description |
|--------|-------------|
| `settings(array $inputs)` | Create settings header action (shortcut) |
| `getPageSettingsAction(?array $schema)` | Create settings action with optional schema override |
| `getPageSetting(string $key, mixed $default)` | Read a single setting value |
| `getPageSettingsData()` | Get all settings (merged with defaults) |
| `savePageSettings(array $data)` | Persist settings to database |
| `getPageSettingsOrder()` | Get saved item order (for sortable) |
| `resetPageSettingsToDefaults()` | Delete stored settings, revert to defaults |
| `saveAsPageSettingsPreset(string $name, bool $isDefault)` | Save current config as a preset |
| `applyPageSettingsPreset(int $presetId)` | Apply a preset |
| `deletePageSettingsPreset(int $presetId)` | Delete a preset |
| `applyPageSettingsToRole(string $role)` | Bulk-apply to role |
| `applyPageSettingsToTeam(int $teamId, string $teamRelation)` | Bulk-apply to team |

#### Overridable Methods

| Method | Default | Description |
|--------|---------|-------------|
| `getPageSettingsFormSchema()` | `[]` | Define settings form fields |
| `getPageSettingsDefaults()` | Auto from definitions | Default values |
| `getPageSettingsScope()` | `null` | Scope key for per-context settings |
| `pageSettingsLivePreview()` | `false` | Enable reactive live preview |
| `onPageSettingPreviewUpdated($key, $value)` | no-op | Handle live preview changes |
| `onPageSettingsUpdated(array $data)` | no-op | Hook after save |

### CanLogsActivity

Automatic activity logging for Eloquent models. Tracks creates, updates, and deletes with human-readable descriptions.

```php
use Codenzia\ProjectEssentials\Traits\CanLogsActivity;

class Invoice extends Model
{
    use CanLogsActivity;

    // Optional: custom field-to-relation mappings
    public function getActivityLogFieldMappings(): array
    {
        return ['client_user_id' => 'client'];
    }

    // Optional: custom friendly names
    public function getActivityLogFriendlyNames(): array
    {
        return ['client_user_id' => 'Client'];
    }
}
```

Requires an `activity_logs` table with columns: `user_id`, `model_id`, `model`, `current_data`, `new_data`, `description`, `created_at`, `updated_at`.

### HasCreatedUpdatedViewComponent

Mixin trait for `CreatedUpdatedColumn` and `CreatedUpdatedEntry`. Provides a `limit()` method to truncate displayed usernames.

```php
use Codenzia\ProjectEssentials\Traits\HasCreatedUpdatedViewComponent;

class MyColumn extends Column
{
    use HasCreatedUpdatedViewComponent;
}

// Usage
MyColumn::make('timestamps')->limit(15);
```

### HasColoredEnumViewComponent

Mixin trait for Filament columns/entries to support enum-based coloring. Used internally by `ColoredEnumColumn` and `ColoredEnumEntry`.

### HasProgressBarViewComponent

Configurable progress bar rendering with label, alignment, font sizes, and value visibility. Used internally by `ProgressBarColumn` and `ProgressBarEntry`.

### HasUserAvatarViewComponent

Size configuration mixin for avatar columns/entries. Used internally by `UserAvatarColumn` and `UserAvatarEntry`.

---

## Form Components

### IconColoredEnumSelect

A rich Filament Select with colored icons for PHP enums using `IconColoredEnum`. Supports two visual modes: **icon + colored text** (default) and **badge** (colored background pills with icon).

```php
use Codenzia\ProjectEssentials\Forms\Components\IconColoredEnumSelect;

// Default mode: icon + colored text in dropdown
IconColoredEnumSelect::make('status')
    ->enumClass(StatusEnum::class)
    ->label('Status')

// Badge mode: colored background pills with icon
IconColoredEnumSelect::make('proficiency')
    ->enumClass(ProficiencyLevelEnum::class)
    ->badge()
```

| Method | Description |
|--------|-------------|
| `enumClass(string)` | The enum class (must use `IconColoredEnum` trait) |
| `badge(bool)` | Enable badge mode — renders options as colored background pills with icon (default: `false`) |

### DatePickerWithHint

DatePicker that auto-applies `config('app.date_format')` with a human-readable format hint icon.

```php
use Codenzia\ProjectEssentials\Forms\Components\DatePickerWithHint;

// Default: hint icon next to the label (top)
DatePickerWithHint::make('start_date')
    ->label('Start Date')

// Prefix icon inside the input — avoids extra label-row height in table repeaters
DatePickerWithHint::make('due_date')
    ->hintPosition('left')

// Suffix icon inside the input
DatePickerWithHint::make('due_date')
    ->hintPosition('right')
```

| Method | Values | Description |
|--------|--------|-------------|
| `hintPosition(string)` | `'top'` (default), `'left'`, `'right'` | `top` — hint icon with tooltip next to label; `left` — prefix icon inside input; `right` — suffix icon inside input |

### PercentageSlider

Interactive slider for 0-100% values with +/- buttons and drag support.

```php
use Codenzia\ProjectEssentials\Forms\Components\PercentageSlider;

PercentageSlider::make('progress')
    ->label('Completion')
```

### CounterInput

Stepper number input with increment/decrement buttons.

```php
use Codenzia\ProjectEssentials\Forms\Components\CounterInput;

CounterInput::make('quantity')
    ->label('Quantity')
```

### DateTimePickerWithHint

DateTimePicker that auto-applies `config('app.datetime_format')` with a human-readable format hint.

```php
use Codenzia\ProjectEssentials\Forms\Components\DateTimePickerWithHint;

DateTimePickerWithHint::make('start_at')
    ->label('Start At')
```

### DateRangePicker

A date range picker field with From/To inputs, locale-aware display formatting, and a clear button. Matches Filament's native DatePicker styling.

```php
use Codenzia\ProjectEssentials\Forms\Components\DateRangePicker;

DateRangePicker::make('date_range')
    ->label('Date Range')

// With custom date format and placeholder
DateRangePicker::make('period')
    ->dateFormat('Y-m-d')
    ->placeholder('Select period')
```

State structure: `['from' => '2024-01-01', 'to' => '2024-12-31']`

| Method | Description |
|--------|-------------|
| `dateFormat(string)` | PHP date format string (default: `config('app.date_format', 'd M, Y')`) |
| `placeholder(string)` | Placeholder text when no dates selected |

### CardRepeater

A card-based repeater field that displays items as read-only cards with inline editing. Supports two modes: **card mode** (read-only cards with edit-on-click) and **inline mode** (form fields always visible). Full relationship support for HasMany and BelongsToMany.

```php
use Codenzia\ProjectEssentials\Forms\Components\CardRepeater;

// Card mode with relationship
CardRepeater::make('milestones')
    ->relationship()
    ->gridColumns(1)
    ->cardSchema(fn (Schema $schema) => $schema->schema([
        View::make('milestone-card'),
    ]))
    ->formSchema([
        TextInput::make('title')->required(),
        DatePicker::make('due_date'),
    ])
    ->addActionLabel('Add milestone')
    ->addActionIcon('heroicon-o-plus')
    ->deletable()

// Inline mode (no cardSchema — fields always visible)
CardRepeater::make('items')
    ->formSchema([
        TextInput::make('name')->required(),
        TextInput::make('quantity')->numeric(),
    ])
    ->gridColumns(1)
    ->deletable()

// BelongsToMany with grid layout
CardRepeater::make('members')
    ->relationship()
    ->gridColumns(3)
    ->cardSchema(fn (Schema $schema) => $schema->schema([
        View::make('member-card'),
    ]))
    ->formSchema([
        Select::make('user_id')->relationship('user', 'name'),
    ])
    ->disableOptionsWhenSelectedInSiblingItems('user_id')

// BelongsToMany with custom save logic (pivot data)
CardRepeater::make('members')
    ->relationship('members', function ($relationship, $state) {
        $relationship->sync(collect($state)->mapWithKeys(fn ($item) => [
            $item['id'] => ['role' => $item['role']],
        ]));
    })

// Table-style layout with a shared header row above all cards
CardRepeater::make('goals')
    ->relationship()
    ->gridColumns(1)
    ->tableHeader('forms.components.goal-card-header')
    ->cardSchema(fn (Schema $schema) => $schema->schema([
        View::make('forms.components.goal-card'),
    ]))
```

**Note on `tableHeader()` vs inline `@if($isFirst)` headers.** It's tempting to put a column header inside the first card's view using `@if($isFirst)`, but that makes the first card taller than the rest and breaks any absolute positioning the wrapper does (notably the hover-only delete button alignment). Use `tableHeader()` instead — the header renders once above the grid and every card wrapper stays the same height.

| Method | Description |
|--------|-------------|
| `formSchema(array\|Closure)` | Form fields for add/edit mode |
| `cardSchema(Closure)` | Read-only card display (enables card mode) |
| `cardActions(array\|Closure)` | Custom actions on each card |
| `showCardActionsOnHover(bool)` | Show card actions only on hover (default: `true`) |
| `relationship(?string, ?Closure)` | Enable relationship mode with optional custom save |
| `gridColumns(int\|Closure)` | Number of grid columns (default: `1`) |
| `tableHeader(string\|Closure)` | Blade view rendered once above the card grid (e.g. a table-style column header row). Keeps all card wrappers uniform in height so positioned controls (like the hover delete button) align consistently |
| `emptyMessage(string\|Closure)` | Message when no items (default: `'No items yet'`) |
| `addActionLabel(string\|Closure)` | Label for the add button (default: `'Add item'`) |
| `addActionIcon(string\|Closure)` | Icon for the add button |
| `addActionAlignment(string\|Closure)` | Alignment: `'left'`, `'center'`, `'right'` (default: `'left'`) |
| `deletable(bool\|Closure)` | Allow deleting items (default: `true`) |
| `editable(bool\|Closure)` | Allow editing items (default: `true`) |
| `disableOptionsWhenSelectedInSiblingItems(string)` | Disable selected options in sibling Select fields |

---

## Table Filters

### DateRangeFilter

A table filter that uses DateRangePicker for date range filtering. Automatically builds the query, formats indicators, and supports custom column targeting.

```php
use Codenzia\ProjectEssentials\Tables\Filters\DateRangeFilter;

// Basic — filters by the column matching the filter name
DateRangeFilter::make('created_at')

// With custom column and placeholder
DateRangeFilter::make('date_filter')
    ->column('published_at')
    ->placeholder('Published date')
```

| Method | Description |
|--------|-------------|
| `column(string)` | Database column to filter (default: filter name) |
| `placeholder(string)` | Placeholder text in the picker |

---

## Table Columns

### ColoredEnumColumn

TextColumn with automatic enum-based coloring and icons.

```php
use Codenzia\ProjectEssentials\Tables\Columns\ColoredEnumColumn;

ColoredEnumColumn::make('status')
    ->enum(StatusEnum::class)
    ->label('Status')
```

### ProgressBarColumn

Visual progress bar column for tables.

```php
use Codenzia\ProjectEssentials\Tables\Columns\ProgressBarColumn;

ProgressBarColumn::make('progress')
    ->progressLabel('Completion')
    ->alignValue('right')
    ->labelFontSize('sm')
    ->valueFontSize('xs')
```

| Method | Description |
|--------|-------------|
| `progressLabel(string)` | Label text above the bar |
| `alignValue(string)` | `'left'`, `'center'`, `'right'`, `'before'`, `'after'` |
| `hideValue(bool)` | Hide percentage text |
| `labelFontSize(string)` | Font size: `'xs'`, `'sm'`, `'md'`, `'lg'`, `'xl'` |
| `valueFontSize(string)` | Font size for the value |

### HtmlColumn

Renders raw HTML content in a table column.

```php
use Codenzia\ProjectEssentials\Tables\Columns\HtmlColumn;

HtmlColumn::make('custom')
    ->html(fn ($record) => '<strong>' . e($record->name) . '</strong>')
```

### UserAvatarColumn

Displays user avatars with automatic fallback. Resolves user from relationships automatically.

```php
use Codenzia\ProjectEssentials\Tables\Columns\UserAvatarColumn;

UserAvatarColumn::make('assignee.avatar')
    ->size('size-10')
    ->label('Assignee')
```

### TagColumn

Displays tags as colored badges with configurable context limits. Supports random or gradient colors.

```php
use Codenzia\ProjectEssentials\Tables\Columns\TagColumn;

TagColumn::make('tags')
    ->label('Tags')
```

### ColoredPillsColumn / ColoredPillsEntry

Generic colored pill badge component for tables and infolists. Each item can have its own color, size, and tooltip. Overflow items are shown in a polished hover card (teleported to body to escape table overflow). Both components share a single blade view.

Supports two modes:
- **Manual** — provide `itemColor()`, `itemTooltip()`, `itemSubtitle()` closures for full control
- **Enum** — pass an `IconColoredEnum`-based enum via `enum()` to auto-resolve colors, tooltips, and subtitles

```php
use Codenzia\ProjectEssentials\Tables\Columns\ColoredPillsColumn;
use Codenzia\ProjectEssentials\Infolists\Components\ColoredPillsEntry;

// Enum mode — colors, tooltips, and subtitles auto-resolved from ProficiencyLevelEnum
ColoredPillsColumn::make('skills')
    ->label('Skills')
    ->visibleLimit(3)
    ->itemLabel(fn ($item) => $item->name)
    ->enum(ProficiencyLevelEnum::class, 'pivot.proficiency')
    ->emptyLabel('No Skills')
    ->hoverLabel(':count more skill|:count more skills')

// Same API for infolists
ColoredPillsEntry::make('skills')
    ->label('Skills')
    ->visibleLimit(3)
    ->itemLabel(fn ($item) => $item->name)
    ->enum(ProficiencyLevelEnum::class, 'pivot.proficiency')
    ->emptyLabel('No Skills')
    ->hoverLabel(':count more skill|:count more skills')

// Manual mode — full control with closures
ColoredPillsColumn::make('categories')
    ->visibleLimit(2)
    ->itemColor(fn ($item) => 'bg-blue-100 border-blue-300 text-blue-700 dark:bg-blue-500/15 dark:border-blue-400/50 dark:text-blue-400')
    ->emptyLabel('No Categories')

// Custom item resolver
ColoredPillsColumn::make('permissions')
    ->items(fn ($record) => $record->roles->flatMap->permissions->unique('id'))
    ->itemLabel(fn ($item) => $item->display_name)
```

| Method | Description |
|--------|-------------|
| `visibleLimit(int)` | Max items shown before `+N` overflow badge (default: 3) |
| `items(Closure)` | Custom resolver `fn($record) => Collection` (default: `$record->{columnName}`) |
| `itemLabel(string\|Closure)` | Label accessor — attribute name or `fn($item) => string` (default: `'name'`) |
| `enum(string, string)` | Auto-resolve colors/tooltips/subtitles from an `IconColoredEnum`. Args: enum class, dot-notation attribute path (e.g. `'pivot.proficiency'`) |
| `itemColor(Closure)` | CSS classes per item `fn($item) => string` — overrides enum colors if both set |
| `itemSize(Closure)` | Size class per item `fn($item) => string` (default: `'text-xs'`) |
| `itemTooltip(Closure)` | Tooltip per item `fn($item) => ?string` — overrides enum tooltips if both set |
| `itemSubtitle(Closure)` | Subtitle shown next to label in hover card `fn($item) => ?string` — overrides enum subtitles |
| `emptyLabel(string)` | Text when collection is empty (default: `'No Items'`) |
| `hoverLabel(string)` | Singular\|plural for hover card header (default: `':count more item\|:count more items'`) |
| `tailwindColorToBadgeClasses(string)` | Static helper: converts a Tailwind color name (e.g. `'danger'`, `'blue'`) to full light/dark badge CSS classes |

### PriorityColumn

Displays priority with icon and color from your enum. Works with any enum implementing `HasLabel`, `HasColor`, and `HasIcon` contracts, or with static `color()`/`icon()` methods.

```php
use Codenzia\ProjectEssentials\Tables\Columns\PriorityColumn;

PriorityColumn::make('priority')
    ->label('Priority')
    ->textColor('#FF5733') // Optional: override the enum color
```

### CircularProgressBar

SVG circular progress bar with gradient colors. Reads `real_progress` from the record.

```php
use Codenzia\ProjectEssentials\Tables\Columns\CircularProgressBar;

CircularProgressBar::make('progress')
    ->label('Progress')
```

### CreatedUpdatedColumn

Shows created/updated timestamps with user avatars. Requires `created_at`, `updated_at` timestamps and optionally `createdByUser`/`updatedByUser` relationships on the model.

```php
use Codenzia\ProjectEssentials\Tables\Columns\CreatedUpdatedColumn;

CreatedUpdatedColumn::make('timestamps')
    ->label('Created & Updated')
    ->limit(15) // Truncate usernames to 15 chars
```

### StateSwitcher

A toggle button column with configurable on/off states. Works with string-backed enums, plain strings, integers, or booleans.

```php
use Codenzia\ProjectEssentials\Tables\Columns\StateSwitcher;

// With enum values
StateSwitcher::make('status')
    ->onState(StatusEnum::ACTIVE)
    ->offState(StatusEnum::INACTIVE)
    ->onLabel(__('Active'))
    ->offLabel(__('Inactive'))
    ->label('State')

// With plain strings
StateSwitcher::make('status')
    ->onState('enabled')
    ->offState('disabled')
    ->onLabel('On')
    ->offLabel('Off')
```

| Method | Description |
|--------|-------------|
| `onState(mixed)` | Value to set when toggled on (default: `'Active'`) |
| `offState(mixed)` | Value to set when toggled off (default: `'Inactive'`) |
| `onLabel(string)` | Button label when on (default: `'Active'`) |
| `offLabel(string)` | Button label when off (default: `'Inactive'`) |

---

## Infolist Entries

### ColoredEnumEntry

TextEntry with enum-based coloring for infolists.

```php
use Codenzia\ProjectEssentials\Infolists\Components\ColoredEnumEntry;

ColoredEnumEntry::make('status')
    ->enum(StatusEnum::class)
```

### ProgressBarEntry

Progress bar entry for infolists.

```php
use Codenzia\ProjectEssentials\Infolists\Components\ProgressBarEntry;

ProgressBarEntry::make('progress')
    ->progressLabel('Done')
    ->alignValue('after')
```

### UserAvatarEntry

User avatar display for infolists.

```php
use Codenzia\ProjectEssentials\Infolists\Components\UserAvatarEntry;

UserAvatarEntry::make('assignee.avatar')
    ->size('size-12')
```

### TagEntry

Tag display for infolists. Same rendering as `TagColumn`.

```php
use Codenzia\ProjectEssentials\Infolists\Components\TagEntry;

TagEntry::make('tags')
    ->label('Tags')
```

### PriorityEntry

Priority display for infolists with icon and color from your enum.

```php
use Codenzia\ProjectEssentials\Infolists\Components\PriorityEntry;

PriorityEntry::make('priority')
    ->label('Priority')
```

### CreatedUpdatedEntry

Created/updated timestamps with user avatars for infolists.

```php
use Codenzia\ProjectEssentials\Infolists\Components\CreatedUpdatedEntry;

CreatedUpdatedEntry::make('timestamps')
    ->label('Created & Updated')
    ->limit(20)
```

---

## Filament Actions

### SplitButtonDropdownAction

A split button with a main action on the left and a dropdown chevron on the right showing all actions. Works in both **page-level** header actions (`getHeaderActions()`) and **table** header/record actions — it auto-detects table context and injects it into child actions when available.

> **Important:** Only use `Action::make()` (`Filament\Actions\Action`) inside the split button. **Do not** use `CreateAction`, `EditAction`, `DeleteAction`, or any other specialized Filament action class. These specialized actions rely on internal Livewire method routing (e.g. `mountAction('create')`) that breaks when nested inside an `ActionGroup`. The primary (left) button will silently fail to fire. Instead, use a regular `Action::make()` with an explicit `->schema()` and `->action()` callback.

```php
use Codenzia\ProjectEssentials\Filament\Actions\SplitButtonDropdownAction;

// Page-level header action
SplitButtonDropdownAction::make([
    Action::make('createProject')
        ->label('New Project')
        ->icon('heroicon-o-plus')
        ->slideOver()
        ->model(Project::class)
        ->schema(ProjectForm::getFields(true))
        ->action(function (array $data) {
            $project = Project::create($data);
        }),
    Action::make('fromTemplate')
        ->label('From Template')
        ->icon('heroicon-o-document-duplicate'),
])

// Table header action (table context is auto-detected)
$table->headerActions([
    SplitButtonDropdownAction::make([
        Tables\Actions\Action::make('edit')->label('Edit'),
        Tables\Actions\Action::make('delete')->label('Delete')->color('danger'),
    ]),
])

// Wrong — CreateAction will not fire from the primary button
SplitButtonDropdownAction::make([
    CreateAction::make(), // Primary button does nothing
    Action::make('other'),
])
```

---

## Helpers

### TailwindHelper

Resolves Tailwind v4 dynamic color class issues. Maps Filament color names to full, statically-analyzable class strings.

```php
use Codenzia\ProjectEssentials\Helpers\TailwindHelper;

TailwindHelper::text('primary');         // "text-primary-500"
TailwindHelper::text('danger', '600');   // "text-danger-600"
TailwindHelper::bg('success', '100');    // "bg-success-100"
TailwindHelper::border('warning');       // "border-warning-500"
```

### DateHelper

```php
use Codenzia\ProjectEssentials\Helpers\DateHelper;

DateHelper::readableDateFormat();          // "dd/mm/yyyy" (based on config)
DateHelper::readableDateTimeFormat();      // "dd/mm/yyyy hh:mm"
DateHelper::getHumanReadableDuration($start, $end); // "30 days (15 left)"
DateHelper::getDelayPeriods();             // [1 => '1 month', 2 => '2 months', ...]
```

### DateRangeHelper

```php
use Codenzia\ProjectEssentials\Helpers\DateRangeHelper;

DateRangeHelper::make('2024-01-01', '2024-12-31');     // "01/01/2024 - 31/12/2024"
DateRangeHelper::makeShort('2024-01-01', '2024-03-15'); // "1 Jan - 15 Mar '24"
DateRangeHelper::parse('01/01/2024 - 31/12/2024');      // ['start_date' => ..., 'end_date' => ...]
```

### FileHelper

```php
use Codenzia\ProjectEssentials\Helpers\FileHelper;

FileHelper::getFileType('photo.jpg');   // "image"
FileHelper::getFileType('report.pdf');  // "document"
FileHelper::getFileType('song.mp3');    // "audio"
FileHelper::getFileType('movie.mp4');   // "video"
```

### FormatHelper

```php
use Codenzia\ProjectEssentials\Helpers\FormatHelper;

FormatHelper::truncateText('Long text here...', 10);  // "Long text ..."
FormatHelper::hexToRgba('#FF5733', 0.8);               // "rgba(255, 87, 51, 0.8)"
FormatHelper::getFormattedIdString(42, 'INV');           // "#INV0042"
```

### FilamentTableHelper

Adds reusable timestamp and userstamp columns to any Filament table.

```php
use Codenzia\ProjectEssentials\Tables\FilamentTableHelper;

// Append to a Table instance
FilamentTableHelper::appendStampColumns($table);

// Or merge with an array of columns
$columns = FilamentTableHelper::withStampColumns([
    TextColumn::make('name'),
    TextColumn::make('email'),
]);

// Add a search filter
$filters = FilamentTableHelper::withSearchFilter([], 'name', 'Search by name');
```

---

## Blade Components

### DropdownCheckList

A multi-select dropdown with checkboxes, optional search, avatar support, and enum integration.

```blade
{{-- Simple options --}}
<x-project-essentials::dropdown-check-list
    :options="[
        ['value' => 'a', 'label' => 'Option A'],
        ['value' => 'b', 'label' => 'Option B'],
    ]"
    label="Filter"
    icon="heroicon-o-funnel"
    x-model="selectedValues"
/>

{{-- With enum --}}
<x-project-essentials::dropdown-check-list
    :enum-class="App\Enums\StatusEnum::class"
    label="Status"
    :searchable="true"
/>

{{-- Rich options with avatars --}}
<x-project-essentials::dropdown-check-list
    :options="[
        ['value' => '1', 'label' => 'John', 'avatar' => '/avatars/john.jpg', 'subtitle' => 'Developer'],
    ]"
    label="Team"
    :searchable="true"
/>
```

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `options` | array | `[]` | Array of `['value' => ..., 'label' => ...]` |
| `enumClass` | string | `null` | Enum class (auto-generates options) |
| `label` | string | `''` | Trigger button label |
| `icon` | string | `null` | Trigger button icon |
| `color` | string | `'gray'` | Button color |
| `allowAll` | bool | `true` | Show "All" radio option |
| `searchable` | bool | `false` | Enable search input |
| `maxHeight` | string | `'14rem'` | Max dropdown height |

### ResponsiveTabs

A responsive tab component that automatically folds overflow tabs into a "More" dropdown when space is limited. Supports Livewire binding, localStorage persistence, badges, icons, disabled tabs, and two layout modes.

```blade
{{-- Inline layout (default) — icon and label side by side --}}
<x-project-essentials::responsive-tabs
    :tabs="[
        'overview' => ['label' => 'Overview', 'icon' => 'heroicon-o-home'],
        'tasks' => ['label' => 'Tasks', 'icon' => 'heroicon-o-clipboard', 'badge' => 12],
        'settings' => ['label' => 'Settings', 'icon' => 'heroicon-o-cog'],
    ]"
    wire:model="activeTab"
    align="left"
    persist="my-page-tabs"
/>

{{-- Stacked layout — icon on top, label below (compact, fits more tabs) --}}
<x-project-essentials::responsive-tabs
    :tabs="$this->getTabs()"
    wire:model="activeTab"
    layout="stacked"
/>

{{-- With colored icons --}}
<x-project-essentials::responsive-tabs
    :tabs="[
        'budget' => ['label' => 'Budget', 'icon' => 'heroicon-o-banknotes', 'iconColor' => 'text-emerald-500'],
        'risks' => ['label' => 'Risks', 'icon' => 'heroicon-o-exclamation-triangle', 'iconColor' => 'text-amber-500'],
        'files' => ['label' => 'Files', 'icon' => 'heroicon-o-folder', 'iconColor' => 'text-blue-400'],
    ]"
    wire:model="activeTab"
/>
```

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `tabs` | array | `[]` | Array of tab definitions (keyed or indexed) |
| `active` | string | `null` | Initial active tab ID (when not using `wire:model`) |
| `align` | string | `'center'` | Tab alignment: `'left'`, `'center'`, or `'right'` |
| `moreLabel` | string | `'More'` | Label for the overflow dropdown button |
| `persist` | string | `null` | localStorage key for tab persistence |
| `layout` | string | `'inline'` | `'inline'` (icon + label side by side) or `'stacked'` (icon above label — more compact) |

**Tab options** — each entry in the `tabs` array supports:

| Property | Type | Required | Description |
|----------|------|----------|-------------|
| `id` | string | Yes* | Unique identifier (*auto from key if keyed array) |
| `label` | string | Yes | Display text for the tab |
| `icon` | string | No | Icon component name (e.g. `'heroicon-o-home'`) |
| `iconColor` | string | No | Tailwind text color class for the icon (e.g. `'text-amber-500'`). Overridden by primary color when the tab is active. |
| `badge` | int/string | No | Badge value (rendered as Filament badge or floating pill in stacked mode) |
| `params` | array | No | Custom parameters passed through on tab change |
| `disabled` | bool | No | Whether the tab is disabled |

**Stacked layout** is ideal when you have many tabs (8+). It renders each tab as a vertical column with the icon on top and label below, reducing per-tab width by ~40% so more tabs fit before overflowing into the "More" dropdown. Badges are rendered as floating pills on the icon's top-right corner.

Requires importing the JS component in your app.js:
```js
import '../../vendor/codenzia/project-essentials/resources/js/responsive-tabs.js';
```

### LoadingSpinner

Advanced loading overlay with 4 visual variants, configurable blur, opacity, and theme.

```blade
{{-- Basic --}}
<x-project-essentials::loading-spinner wire:loading />

{{-- Full configuration --}}
<x-project-essentials::loading-spinner
    :show="$isLoading"
    variant="orbital"
    size="lg"
    blur="md"
    opacity="75"
    message="Processing..."
    :show-progress="true"
    theme="auto"
/>
```

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `show` | bool | `false` | Initial visibility |
| `variant` | string | `'elegant'` | `'minimal'`, `'elegant'`, `'orbital'`, `'pulse'` |
| `size` | string | `'md'` | `'sm'`, `'md'`, `'lg'`, `'xl'` |
| `blur` | string | `'md'` | Backdrop blur: `'none'`, `'sm'`, `'md'`, `'lg'`, `'xl'` |
| `opacity` | string | `'0'` | Background opacity: `'0'`-`'95'` |
| `message` | string | `'Please wait'` | Loading message |
| `fullscreen` | bool | `true` | Cover entire viewport |
| `showProgress` | bool | `false` | Indeterminate progress bar |
| `theme` | string | `'auto'` | `'auto'`, `'light'`, `'dark'` |
| `delay` | int | `200` | Delay (ms) before showing |

### LocaleSwitcher

A standard language-switcher dropdown that renders identically inside a Filament panel **and** on any public Blade page. It ships its own scoped CSS — no `flag-icons` library and no reliance on the host's Tailwind build (the failure mode that breaks utility-class-based switchers on public sites). It shows a globe glyph + the current code, and lists locales by native name with a checkmark on the active one. Dark mode and RTL are handled automatically.

```blade
{{-- Zero-config: auto-resolves locales (filament-panel-base → config('app.available_locales') → app locale) --}}
<x-project-essentials::locale-switcher />

{{-- Explicit locales + alignment --}}
<x-project-essentials::locale-switcher
    :locales="['en' => [], 'ar' => []]"
    align="start"
/>

{{-- Passing filament-panel-base's active locales --}}
<x-project-essentials::locale-switcher
    :locales="\Codenzia\FilamentPanelBase\Middleware\SetLocale::getLocales()"
/>
```

Requires a named route to switch locale (default `locale.switch`, receiving the locale code):

```php
Route::get('/locale/{locale}', function (string $locale) {
    session()->put('locale', $locale);
    return back();
})->name('locale.switch');
```

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `locales` | array\|null | auto | `['en' => [...], 'ar' => [...]]` or list `['en','ar']`. Entries may carry `native`/`dir`; missing values are filled from a built-in native-name map. |
| `currentLocale` | string\|null | `app()->getLocale()` | The active locale code. |
| `switchRoute` | string | `'locale.switch'` | Named route to link each locale to. |
| `align` | string | `'end'` | Dropdown alignment: `'start'` or `'end'`. |

The dropdown only renders when more than one locale is available. Alpine.js (bundled with Filament/Livewire) powers the open/close.

### Banner

Top-of-page notification banner with success/danger/warning styles.

```blade
<x-project-essentials::banner />

{{-- Trigger from Livewire/Alpine --}}
<script>
    window.dispatchEvent(new CustomEvent('banner-message', {
        detail: { style: 'success', message: 'Saved successfully!' }
    }));
</script>
```

### UserAvatar

Displays user avatars with automatic fallback. Works standalone or within Filament Column/Entry context.

```blade
<x-project-essentials::user-avatar :user="$user" size="size-10" />
```

### DropdownLink

A styled link for use inside dropdown menus.

```blade
<x-project-essentials::dropdown-link href="/profile">
    My Profile
</x-project-essentials::dropdown-link>
```

### Progress

A circular progress indicator with SVG arc and gradient colors.

```blade
<x-project-essentials::progress
    :progress="75"
    color="primary"
    label="Completion"
    :show-text="true"
/>
```

### Pagination

```blade
{{ $items->links('project-essentials::components.pagination') }}
```

### IconPicker (Form Component)

```php
use Codenzia\ProjectEssentials\Forms\Components\IconPicker;

IconPicker::make('icon')
    ->label('Icon')
    ->required()
```

---

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
