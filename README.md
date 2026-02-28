# Project Essentials

Essential UI components, form components, and utilities for Laravel and Filament v4 projects — including progress indicators, carousels, pagination, and an icon picker.

## Features

- **Progress Component** — Circular SVG progress indicator with gradient colors
- **Carousel Component** — Swiper.js-powered carousel for Blade templates
- **CarouselEntry** — Filament infolist entry with dynamic card schemas and full Swiper configuration
- **Pagination** — Custom Laravel pagination view with RTL and dark mode support
- **IconPicker** — Filament form select with 45+ categorized Heroicons
- **Dark Mode** — All components support dark mode
- **RTL Support** — Right-to-left layout support

## Requirements

- PHP 8.3+
- Laravel 12+
- Filament 4.x

## Installation

Install via Composer:

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

## Components

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

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `progress` | float | `0` | Progress percentage (0-100) |
| `color` | string | `'primary'` | CSS color variable name |
| `label` | string | `'Progress'` | Display label |
| `showText` | bool | `true` | Show percentage text |

### Carousel (Blade)

A simple Swiper.js carousel for Blade templates.

```blade
<x-project-essentials::carousel
    :slides="$slides"
    :autoplay="true"
    :indicators="true"
    :controls="true"
/>
```

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `slides` | array | `[]` | Array of slide content |
| `autoplay` | bool | `false` | Enable autoplay |
| `indicators` | bool | `true` | Show pagination dots |
| `controls` | bool | `true` | Show prev/next arrows |

### CarouselEntry (Filament Infolist)

An advanced carousel component for Filament infolists with dynamic card schemas.

```php
use Codenzia\ProjectEssentials\View\Components\CarouselEntry;

CarouselEntry::make('items')
    ->slidesPerView(3)
    ->navigation()
    ->pagination()
    ->autoplay()
    ->autoplayDelay(3000)
    ->effect('slide')
    ->height(300)
    ->cardSchema(function (Schema $schema, ?Model $record) {
        return $schema->components([
            TextEntry::make('title'),
            TextEntry::make('description'),
            ImageEntry::make('image'),
        ]);
    })
```

**Available methods:**

| Method | Description |
|--------|-------------|
| `slidesPerView(int)` | Number of visible slides |
| `centeredSlides(bool)` | Center active slide |
| `height(int)` | Container height in pixels |
| `navigation(bool)` | Show prev/next arrows |
| `pagination(bool)` | Show pagination |
| `paginationType(string)` | `'bullets'`, `'fraction'`, `'progressbar'` |
| `paginationClickable(bool)` | Clickable pagination bullets |
| `scrollbar(bool)` | Show scrollbar |
| `autoplay(bool)` | Enable autoplay |
| `autoplayDelay(int)` | Autoplay delay in ms |
| `effect(string)` | `'slide'`, `'fade'`, `'cube'`, `'coverflow'`, `'flip'`, `'cards'` |
| `cardSchema(Closure)` | Dynamic schema builder for each slide |

### Pagination

A custom pagination view with mobile-friendly layout, RTL support, and dark mode.

```blade
{{ $items->links('project-essentials::components.pagination') }}
```

Features:
- Mobile: simplified prev/next with result count
- Desktop: full page numbers with prev/next
- Active page highlighted with brand color
- RTL-aware layout

### IconPicker (Form Component)

A searchable Filament select field pre-loaded with 45+ categorized Heroicons.

```php
use Codenzia\ProjectEssentials\Forms\Components\IconPicker;

IconPicker::make('icon')
    ->label('Icon')
    ->required()
```

Categories include: Education, Buildings, Location, Transport, Health, Shopping, Communication, Nature, Utilities, Recreation, People, and more.

Returns the icon string value (e.g., `'heroicon-o-home'`).

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

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
