# Changelog

All notable changes to Confessionnal will be documented in this file.

## v1.0.0

Initial release of the Confessionnal form and survey builder for Filament v5.

### Features

- **Two form modes**: conversational (one field per screen) and standard (grouped fields per page)
- **Section-based structure** with interstitial intros, randomised section ordering, and reference images (ChambreNoir)
- **Field types**: text, textarea, select, radio, checkbox, scale, date, file upload
- **Conditional logic**: show/hide fields based on prior answers with equals, not_equals, contains, not_contains, is_filled, is_empty operators
- **Multi-language support** via Spatie Translatable for labels, help text, placeholders, and options
- **Generic submissions** stored as JSON with optional **target model mapping** to write into Eloquent models on submit
- **CSV export** with section-prefixed headers for duplicate labels
- **Query string capture** (all, whitelist, or none) stored in submission meta
- **Completion providers**: abstracted provider classes for Prolific, Cint, Toluna, MTurk, and a Generic catch-all. Automatic detection via URL query parameters, static or dynamic completion codes, and provider-specific redirects. Extensible via custom provider classes.
- **Lightweight analytics**: daily aggregate tables for views, starts, completions, and per-page drop-off
- **Preview mode** via signed URL from the Filament admin panel
- **Optional progress bar** with section indicator, configurable at form and block level
- **Atelier block integration**: embed forms on Atelier-managed pages (optional, no hard dependency)
- **Themeable** via `--cfnl-*` CSS custom properties for colours, typography, spacing, and inputs (no build step)
