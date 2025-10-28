Project Essentials is a lightweight Laravel package that provides commonly used UI components, configuration and migrations to help bootstrap new projects quickly. It offers publishable views and a small set of reusable Blade components (for example, a progress indicator) that are easy to customize through component props or by publishing and editing the package views/config.

Use the included components directly in your Blade templates or publish the views/config to adapt them to your project's style. Example usage:

```blade
<x-project-essentials::progress :progress="$progress" color="primary" :label="false" />
```

## Installation

You can install the package via composer:

```bash
composer require codenzia/project-essentials
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="project-essentials-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="project-essentials-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="project-essentials-views"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
 <x-project-essentials::progress :progress="$progress" color="primary" :label="false" />
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [:author_name](https://github.com/:author_username)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
