# Theming

The fill runtime ships with a clean default style. Every visual property is exposed as a CSS custom property, so you can match your website's design without touching the Blade templates.

## CSS custom properties

Override any of these on `:root` (or a more specific selector) to restyle the form:

### Core

| Variable | Default | Description |
|---|---|---|
| `--cfnl-font-family` | `system-ui, -apple-system, sans-serif` | Font stack |
| `--cfnl-max-width` | `640px` | Max width of the form card |
| `--cfnl-bg` | `#f8fafc` | Page background |
| `--cfnl-color` | `#1e293b` | Primary text colour |
| `--cfnl-color-muted` | `#64748b` | Help text, subtext, completion message |
| `--cfnl-color-hint` | `#94a3b8` | Section indicator, Enter hint |
| `--cfnl-error` | `#ef4444` | Validation errors, required asterisk |

### Brand / accent

| Variable | Default | Description |
|---|---|---|
| `--cfnl-primary` | `#3b82f6` | Primary button, focus ring, active scale, progress bar |
| `--cfnl-primary-hover` | `#2563eb` | Primary button hover state |
| `--cfnl-primary-light` | `#f0f7ff` | Option hover background |
| `--cfnl-primary-ring` | `rgba(59, 130, 246, 0.1)` | Focus ring shadow |

### Inputs

| Variable | Default | Description |
|---|---|---|
| `--cfnl-input-bg` | `white` | Input/textarea/select background |
| `--cfnl-input-border` | `#cbd5e1` | Input border colour |
| `--cfnl-input-radius` | `0.5rem` | Border radius for inputs, options, images |
| `--cfnl-option-border` | `#e2e8f0` | Border for radio/checkbox/scale cards |

### Buttons

| Variable | Default | Description |
|---|---|---|
| `--cfnl-btn-radius` | `0.5rem` | Button border radius |
| `--cfnl-btn-secondary-bg` | `#e2e8f0` | Back button background |
| `--cfnl-btn-secondary-color` | `#475569` | Back button text |
| `--cfnl-btn-secondary-hover` | `#cbd5e1` | Back button hover |

### Progress bar

| Variable | Default | Description |
|---|---|---|
| `--cfnl-progress-bg` | `#e2e8f0` | Track background |
| `--cfnl-progress-fill` | `var(--cfnl-primary)` | Fill colour (follows primary by default) |

## Quick example: brand colour override

To change the accent colour across the entire form, override `--cfnl-primary` and its related properties. The simplest approach is a `<style>` block in the published layout:

```css
:root {
    --cfnl-primary: #10b981;
    --cfnl-primary-hover: #059669;
    --cfnl-primary-light: #ecfdf5;
    --cfnl-primary-ring: rgba(16, 185, 129, 0.1);
}
```

## Dark mode example

```css
:root {
    --cfnl-bg: #0f172a;
    --cfnl-color: #e2e8f0;
    --cfnl-color-muted: #94a3b8;
    --cfnl-input-bg: #1e293b;
    --cfnl-input-border: #334155;
    --cfnl-option-border: #334155;
    --cfnl-btn-secondary-bg: #334155;
    --cfnl-btn-secondary-color: #cbd5e1;
    --cfnl-btn-secondary-hover: #475569;
}
```

## Three levels of customisation

### Level 1: CSS variables only (no publish needed)

Add a `<style>` tag to your app layout or load a stylesheet that overrides the `--cfnl-*` variables. This works without publishing any views because the variables are defined with defaults on `:root`.

If your app already has a global stylesheet, just add the overrides there:

```css
/* resources/css/app.css */
:root {
    --cfnl-primary: #7c3aed;
    --cfnl-primary-hover: #6d28d9;
    --cfnl-font-family: 'Inter', sans-serif;
}
```

Then include it in the published layout (see Level 2).

### Level 2: Publish and edit the layout

```bash
php artisan vendor:publish --tag="confessionnal-views"
```

This copies the Blade views to `resources/views/vendor/confessionnal/`. Edit `layouts/fill.blade.php` to:

- Add your own CSS or Tailwind stylesheet via `<link>` or `@vite`
- Add a site header/footer around `{{ $slot }}`
- Set CSS variables in the `<style>` block
- Include scripts, analytics, or meta tags

The component views (`livewire/form-fill.blade.php`, `partials/field.blade.php`) can also be edited for structural changes.

### Level 3: Full template override

For complete control, override individual Blade files. All form elements use `.confessionnal-*` class names, so your CSS can target them precisely. The view files:

| View | Purpose |
|---|---|
| `layouts/fill.blade.php` | HTML shell, all CSS, head/body |
| `livewire/form-fill.blade.php` | Step rendering, navigation, progress bar, completion screen |
| `partials/field.blade.php` | Individual field type rendering (switch on field type) |

## CSS class reference

All classes are prefixed with `confessionnal-` to avoid collisions with your site's styles:

| Class | Element |
|---|---|
| `.confessionnal-container` | Outer wrapper |
| `.confessionnal-card` | Content card (max-width container) |
| `.confessionnal-progress-wrapper` | Progress bar wrapper |
| `.confessionnal-progress` | Progress track |
| `.confessionnal-progress-bar` | Progress fill |
| `.confessionnal-section-indicator` | "Section X of Y" text |
| `.confessionnal-intro` | Section interstitial |
| `.confessionnal-field` | Field wrapper |
| `.confessionnal-options` | Radio/checkbox option group |
| `.confessionnal-scale` | Scale option group |
| `.confessionnal-scale-labels` | Low/mid/high labels under scale |
| `.confessionnal-nav` | Navigation button row |
| `.confessionnal-btn` | Base button |
| `.confessionnal-btn-primary` | Primary (Next/Submit/Continue) |
| `.confessionnal-btn-secondary` | Secondary (Back) |
| `.confessionnal-complete` | Thank-you screen |
| `.confessionnal-context-image` | Reference image wrapper |
| `.confessionnal-file-upload` | File upload wrapper |
| `.confessionnal-preview-banner` | Preview mode banner |
