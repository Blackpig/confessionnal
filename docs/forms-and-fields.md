# Forms and Fields

## Form modes

Each form has a **mode** that controls how questions are presented to respondents:

| Mode | Behaviour |
|---|---|
| **Conversational** | One field per screen, Typeform-style. Progress bar, keyboard nav (Enter to advance), smooth transitions. |
| **Standard** | All fields on a page shown together, traditional multi-field form layout. Still paginated by page. |

Both modes share the same data model. In conversational mode, each field becomes its own step. In standard mode, all fields on a page are grouped into a single step.

## Sections

A form is organised into ordered **Sections**. Each section has:

- **Title** (translatable) and **Subtext** (translatable), displayed as an interstitial intro screen before the section's questions.
- **Randomisation group** (optional). Sections sharing a group value are shuffled per respondent to control for presentation-order bias. See [Advanced: Randomised sections](advanced.md#randomised-section-order).

Sections contain one or more **Pages**.

## Pages

A page groups fields together. In standard mode, all fields on a page render as one step. In conversational mode, each field becomes its own step regardless of page grouping.

Pages can optionally have a **reference image** (via ChambreNoir) displayed alongside questions.

## Field types

| Type | Key | Description |
|---|---|---|
| Short Text | `text` | Single-line text input |
| Long Text | `textarea` | Multi-line textarea |
| Dropdown | `select` | Single-choice dropdown |
| Radio Buttons | `radio` | Single-choice radio group |
| Checkboxes | `checkbox` | Multi-choice checkboxes (stored as array) |
| Scale | `scale` | Numeric range (configurable min/max with optional low/mid/high labels) |
| Date | `date` | Date picker |
| File Upload | `file_upload` | File upload via ChambreNoir |

## Field configuration

Each field supports:

- **Label** (translatable), **Help text** (translatable), **Placeholder** (translatable)
- **Key**: unique identifier within the form, used in answers JSON and wire:model bindings. Auto-generated from label, but editable.
- **Required**: toggle
- **Validation rules**: Laravel validation rules as tags (e.g. `email`, `min:3`, `max:255`). Applied server-side in addition to the built-in type rules.
- **Options**: label/value pairs for select, radio, and checkbox fields.
- **Scale configuration**: min value, max value, low/mid/high labels (for scale fields only).
- **Conditional logic**: show/hide based on another field's answer. See [Advanced: Conditional logic](advanced.md#conditional-logic).

## Translatable content

Form names, descriptions, section titles/subtext, and field labels/help text/placeholders all use [Spatie Laravel Translatable](https://github.com/spatie/laravel-translatable). The fill runtime resolves translations based on the locale in the URL (`/forms/{slug}/{locale}`).

## Cloning sections

The Sections table in the admin panel includes a **Clone** action that deep-copies a section with all its pages and fields. Cloned field keys are automatically suffixed to prevent collisions (e.g. `rating` becomes `rating_2`).
