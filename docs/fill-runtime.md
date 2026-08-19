# Fill Runtime

The fill runtime is a standalone Livewire component that renders the public-facing form. It runs outside the Filament admin panel with its own layout and styling.

## Routes

Forms are accessible at:

```
/forms/{slug}
/forms/{slug}/{locale}
```

The route prefix (`forms`) and middleware (`web`) are configurable in `config/confessionnal.php`.

If no locale is provided, the app's current locale is used. The respondent's locale is recorded on the submission.

## How steps work

The component flattens the form's sections, pages, and fields into a linear list of steps:

1. **Section intro** (if the section has a title or subtext)
2. **Question steps**: one per field in conversational mode, one per page in standard mode

Navigation is handled by `next()` and `previous()` methods. Validation runs on each step before advancing.

## Keyboard navigation

In conversational mode, pressing **Enter** advances to the next step. This is intentionally disabled inside textareas and selects so respondents can type normally.

## Validation

Each step validates its visible fields before allowing the respondent to advance. Rules include:

- `required` or `nullable` based on the field's required toggle
- Type-specific rules: `date` for date fields, `array` for checkboxes, `file` for uploads
- Any custom rules added via the validation rules tag input in the builder

Fields hidden by conditional logic are skipped during validation.

## Preview mode

The admin panel's Edit Form page has a **Preview** button that opens the form in a new tab. Preview mode:

- Uses a signed URL (cannot be shared or accessed without a valid signature)
- Allows unpublished forms to be viewed
- Does not record a submission
- Shows a yellow banner: "Preview mode"

## Completion

On the final step, submitting the form creates a `Submission` record and shows a thank-you screen. If a redirect URL is configured, the respondent is redirected instead.

## File uploads

File upload fields use Livewire's `WithFileUploads` trait. Files are uploaded to the configured disk. The file path is stored as the field's answer value.
