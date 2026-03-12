# Project Essentials

Essential UI components, form fields, table columns, traits, and helpers for Laravel and Filament v4 projects.

## Features

**UI Components**
- **Progress** — Circular SVG progress indicator with gradient colors
- **Carousel / CarouselEntry** — Swiper.js-powered carousel for Blade and Filament infolists
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
- **PriorityColumn / PriorityEntry** — Priority display with icon, color, and label from enums
- **CircularProgressBar** — SVG circular progress bar with gradient
- **CreatedUpdatedColumn / CreatedUpdatedEntry** — Created/updated timestamps with user avatars
- **StateSwitcher** — Toggle column with configurable on/off enum states and labels
- **ResponsiveTabs** — Responsive tabs with overflow "More" dropdown
- **LoadingSpinner** — Advanced loading spinner with 4 variants (minimal, elegant, orbital, pulse)
- **Banner** — Top-of-page notification banner with success/danger/warning styles
- **GridLayoutSwitcher** — Session-persistent grid size slider
- **ColumnToggle** — Custom column visibility toggling with session persistence

**Form Components**
- **IconColoredEnumSelect** — Rich select with colored icons for enums
- **DatePickerWithHint** — DatePicker with auto format hint from config
- **DateTimePickerWithHint** — DateTimePicker with auto format hint from config
- **CounterInput** — Stepper number input with +/- buttons

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
- **HasPageSettings** — Per-user page settings modal with cache persistence
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

Adds a per-user settings modal to any Filament page with cache persistence.

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

    // Read a setting value anywhere
    $showArchived = $this->getPageSetting('show_archived', false);
}
```

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

A rich Filament Select with colored icons for PHP enums using `IconColoredEnum`.

```php
use Codenzia\ProjectEssentials\Forms\Components\IconColoredEnumSelect;

IconColoredEnumSelect::make('status')
    ->enumClass(StatusEnum::class)
    ->label('Status')
```

### DatePickerWithHint

DatePicker that auto-applies `config('app.date_format')` with a human-readable format hint.

```php
use Codenzia\ProjectEssentials\Forms\Components\DatePickerWithHint;

DatePickerWithHint::make('start_date')
    ->label('Start Date')
```

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

A split button with a main action on the left and a dropdown chevron on the right showing all actions.

```php
use Codenzia\ProjectEssentials\Filament\Actions\SplitButtonDropdownAction;

SplitButtonDropdownAction::make([
    Action::make('approve')->label('Approve')->color('success'),
    Action::make('reject')->label('Reject')->color('danger'),
    Action::make('defer')->label('Defer')->color('warning'),
])
```

### TableSplitButtonDropdownAction

Same as `SplitButtonDropdownAction` but with table context support for row-level actions.

```php
use Codenzia\ProjectEssentials\Filament\Actions\TableSplitButtonDropdownAction;

TableSplitButtonDropdownAction::make([
    Tables\Actions\Action::make('edit')->label('Edit'),
    Tables\Actions\Action::make('delete')->label('Delete')->color('danger'),
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

A responsive tab component that automatically folds overflow tabs into a "More" dropdown when space is limited. Supports Livewire binding, localStorage persistence, badges, icons, and disabled tabs.

```blade
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
```

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

### Carousel (Blade)

```blade
<x-project-essentials::carousel
    :slides="$slides"
    :autoplay="true"
    :indicators="true"
    :controls="true"
/>
```

### CarouselEntry (Filament Infolist)

```php
use Codenzia\ProjectEssentials\View\Components\CarouselEntry;

CarouselEntry::make('items')
    ->slidesPerView(3)
    ->navigation()
    ->pagination()
    ->autoplay()
    ->cardSchema(function (Schema $schema, ?Model $record) {
        return $schema->components([
            TextEntry::make('title'),
            ImageEntry::make('image'),
        ]);
    })
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
