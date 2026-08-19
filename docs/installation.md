# Installation

## Requirements

- PHP 8.4+
- Laravel 12+
- Filament v5
- Livewire v4

## Install via Composer

```bash
composer require blackpig-creatif/confessionnal
```

## Publish and run migrations

```bash
php artisan vendor:publish --tag="confessionnal-migrations"
php artisan migrate
```

This creates five tables: `confessionnal_forms`, `confessionnal_sections`, `confessionnal_form_pages`, `confessionnal_form_fields`, and `confessionnal_submissions`.

## Publish the config (optional)

```bash
php artisan vendor:publish --tag="confessionnal-config"
```

This publishes `config/confessionnal.php` with the following options:

```php
return [
    // URI prefix for public fill routes: /forms/{slug}/{locale?}
    'route_prefix' => 'forms',

    // Middleware applied to the public fill route
    'middleware' => ['web'],

    // Additional models implementing CanReceiveSubmissions
    // (auto-discovery scans app/Models/ by default)
    'mappable_models' => [],
];
```

## Register the Filament plugin

Add the plugin to your Filament panel provider:

```php
use BlackpigCreatif\Confessionnal\ConfessionnalPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            ConfessionnalPlugin::make(),
        ]);
}
```

## Publish views (optional)

To customise the fill runtime templates or layout:

```bash
php artisan vendor:publish --tag="confessionnal-views"
```

See [Theming](theming.md) for details on styling the public form.
