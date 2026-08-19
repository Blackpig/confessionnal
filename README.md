# Confessionnal

A conversational form and survey builder for [Filament v5](https://filamentphp.com). Build Typeform-style one-question-per-screen surveys or traditional multi-field forms, with sections, conditional logic, multi-language support, and CSV export.

## Features

- **Two form modes**: conversational (one field per screen) and standard (grouped fields per page)
- **Section-based structure** with interstitial intros, optional reference images, and randomised ordering
- **Field types**: text, textarea, select, radio, checkbox, scale, date, file upload
- **Conditional logic**: show/hide fields based on prior answers
- **Multi-language**: translatable labels, help text, and options via Spatie Translatable. Locale-specific fill links.
- **Generic submissions** stored as JSON, plus optional **target model mapping** to write into real Eloquent models on submit
- **CSV export** with section-prefixed headers for duplicate labels
- **Query string capture** for panel-recruitment services (Prolific, etc.)
- **Completion redirect** with param passthrough
- **Preview mode** via signed URL from the admin panel
- **Themeable** via CSS custom properties (no build step needed)

## Installation

```bash
composer require blackpig-creatif/confessionnal
```

Publish and run migrations:

```bash
php artisan vendor:publish --tag="confessionnal-migrations"
php artisan migrate
```

Register the plugin in your Filament panel:

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

See [docs/installation.md](docs/installation.md) for config options and view publishing.

## Quick start

1. Navigate to **Confessionnal > Forms** in your Filament panel
2. Create a new form, choose a mode (conversational or standard)
3. Add sections with pages and fields via the relation manager
4. Publish the form
5. Visit `/forms/{slug}` to fill it

## Theming

The fill runtime uses CSS custom properties for all colours, typography, and spacing. Override them to match your site:

```css
:root {
    --cfnl-primary: #7c3aed;
    --cfnl-primary-hover: #6d28d9;
    --cfnl-font-family: 'Inter', sans-serif;
    --cfnl-bg: #fafaf9;
}
```

For full customisation (dark mode, custom layout, structural changes), publish the views:

```bash
php artisan vendor:publish --tag="confessionnal-views"
```

See [docs/theming.md](docs/theming.md) for the complete variable reference and examples.

## Documentation

| Topic | Link |
|---|---|
| Installation and config | [docs/installation.md](docs/installation.md) |
| Forms, sections, and fields | [docs/forms-and-fields.md](docs/forms-and-fields.md) |
| Fill runtime and routes | [docs/fill-runtime.md](docs/fill-runtime.md) |
| Submissions and CSV export | [docs/submissions.md](docs/submissions.md) |
| Target model mapping | [docs/target-model-mapping.md](docs/target-model-mapping.md) |
| Theming and CSS variables | [docs/theming.md](docs/theming.md) |
| Conditional logic, randomisation, query capture, preview | [docs/advanced.md](docs/advanced.md) |

## Testing

```bash
composer test
```

## Credits

- [Blackpig Creatif](https://github.com/Blackpig)

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md) for details.
