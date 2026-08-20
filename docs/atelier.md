# Atelier Block Integration

Confessionnal provides an optional block for [Atelier](https://github.com/Blackpig/atelier) that lets you embed forms on any Atelier-managed page. The integration has no hard dependency: the block only registers when Atelier is installed.

## Requirements

- `blackpig-creatif/atelier` installed in your project
- At least one published Confessionnal form

## Setup

### 1. Add the block to a collection

In your block collection class, add `ConfessionnalFormBlock`:

```php
use BlackpigCreatif\Confessionnal\Atelier\ConfessionnalFormBlock;

class HomePageBlocks extends BaseBlockCollection
{
    public function getBlocks(): array
    {
        return [
            HeroBlock::class,
            TextBlock::class,
            // ...
            ConfessionnalFormBlock::class,
        ];
    }
}
```

### 2. Add the block to a page

In the Filament admin, edit an Atelier page and add a "Form (Confessionnal)" block. Select a published form from the dropdown.

The block inherits all of Atelier's common display options (background, spacing, width, dividers).

## Styling

When embedded in an Atelier block, the form inherits your site's page layout but uses Confessionnal's own CSS for form elements. To match your site's design, override the `--cfnl-*` CSS custom properties in your stylesheet, scoped to `.confessionnal-container`:

```css
/* resources/css/app.css */
.confessionnal-container {
    --cfnl-font-family: var(--font-body);
    --cfnl-primary: var(--color-primary);
    --cfnl-primary-hover: var(--color-primary-dark);
    --cfnl-input-border: var(--color-border);
    --cfnl-input-radius: 1rem;
    --cfnl-btn-radius: 9999px;
}
```

### Label styling

Labels support separate typography properties so you can match patterns like uppercase tracking labels common in modern designs:

```css
.confessionnal-container {
    --cfnl-label-font-family: var(--font-accent);
    --cfnl-label-font-size: 0.875rem;
    --cfnl-label-font-weight: 500;
    --cfnl-label-color: var(--color-muted);
    --cfnl-label-letter-spacing: 0.1em;
    --cfnl-label-text-transform: uppercase;
}
```

### Full example (Tailwind v4 site)

```css
.confessionnal-container {
    /* Base typography */
    --cfnl-font-family: var(--font-body);
    --cfnl-label-font-family: var(--font-accent);
    --cfnl-label-font-size: var(--text-sm);
    --cfnl-label-font-weight: 500;
    --cfnl-label-color: var(--color-soft-brown);
    --cfnl-label-letter-spacing: 0.1em;
    --cfnl-label-text-transform: uppercase;
    --cfnl-btn-font-family: var(--font-accent);

    /* Colours */
    --cfnl-color: var(--color-charcoal);
    --cfnl-color-muted: var(--color-soft-brown);
    --cfnl-color-hint: var(--color-soft-brown);
    --cfnl-primary: var(--color-warm-gold);
    --cfnl-primary-hover: var(--color-deep-gold);
    --cfnl-primary-light: var(--color-cream);
    --cfnl-primary-ring: color-mix(in srgb, var(--color-warm-gold) 20%, transparent);

    /* Inputs */
    --cfnl-input-border: var(--color-pale-gold);
    --cfnl-input-radius: 1rem;
    --cfnl-input-padding: 1rem 1.5rem;

    /* Options and buttons */
    --cfnl-option-border: var(--color-pale-gold);
    --cfnl-btn-radius: 9999px;
    --cfnl-btn-secondary-bg: var(--color-pale-gold);
    --cfnl-btn-secondary-color: var(--color-soft-brown);
    --cfnl-btn-secondary-hover: var(--color-warm-gold);
    --cfnl-progress-fill: var(--color-warm-gold);
}
```

See [theming.md](theming.md) for the complete variable reference.

## How it works

The block registers automatically when `AtelierServiceProvider` is detected:

```php
// ConfessionnalServiceProvider::packageBooted()
if (class_exists(AtelierServiceProvider::class)) {
    $this->registerAtelierBlock();
}
```

The block class (`ConfessionnalFormBlock`) extends Atelier's `BaseBlock` and mounts the `confessionnal.form-fill` Livewire component with the selected form's slug. The Livewire component handles all form logic, validation, submission, and target model mapping.

## Target model mapping

Forms embedded via the Atelier block support target model mapping the same way standalone forms do. Configure the target model and field mapping on the Form resource in Filament, and submissions will create or update Eloquent records automatically.

See [target-model-mapping.md](target-model-mapping.md) for setup instructions.

## Notes

- The form's CSS is loaded via an `@once` style block, so it is included exactly once per page regardless of how many form blocks appear.
- The `.confessionnal-container` resets `font-size: 1rem` to insulate the form from any inherited sizing on the parent (e.g. a wrapper with a large `text-*` class).
- Livewire must be available on the frontend. If your site layout includes `@livewireScripts` (or uses Livewire's auto-injection via middleware), the form will work without additional setup.
