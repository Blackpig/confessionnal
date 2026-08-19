# Advanced Features

## Conditional logic

Fields can be shown or hidden based on a prior answer. In the builder, expand the **Conditional logic** section on any field and configure:

- **Show when field**: the field key to watch
- **Operator**: `equals`, `not_equals`, `contains`, `not_contains`, `is_filled`, `is_empty`
- **Value**: the value to compare against (leave blank for `is_filled`/`is_empty`)

In conversational mode, steps where all fields are hidden by unmet conditions are automatically skipped in both directions.

Hidden fields are excluded from validation, so a conditionally hidden required field will not block submission.

### Operators

| Operator | Behaviour |
|---|---|
| `equals` | Loose equality (`==`) |
| `not_equals` | Loose inequality (`!=`) |
| `contains` | For arrays (checkboxes): `in_array`. For strings: `str_contains`. |
| `not_contains` | Inverse of `contains` |
| `is_filled` | Value is not empty |
| `is_empty` | Value is empty |

## Randomised section order

Sections can be assigned a **randomisation group**. All sections sharing the same group value are shuffled as a block per respondent. Ungrouped sections keep their fixed position.

This is useful for A/B-style comparisons where you want to control for presentation-order bias.

### How it works

1. In the builder, set the same **Randomisation group** value on the sections you want shuffled (e.g. "brands").
2. On each page load, sections within the group are shuffled. Pages within a section always keep their internal order.
3. The actual order shown is recorded in the submission's `section_order` column as a JSON array of section IDs.

### Analysing order effects

The `section_order` array lets you check whether presentation order affected results. The CSV export includes the submission ID, which you can join with the `section_order` from the database.

## Query string capture

Confessionnal can capture URL query parameters and store them against the submission. This is designed for panel-recruitment services like Prolific that pass participant IDs and study IDs via the URL.

### Configuration

On the Form edit page, open the **Query Capture** tab:

| Setting | Description |
|---|---|
| **None** | No params captured (default) |
| **Capture all** | All query params are captured |
| **Whitelist** | Only listed param names are captured |

Captured params are stored in `submission.meta.query_params`.

### Example

With capture mode set to "whitelist" and `PROLIFIC_PID, STUDY_ID` configured:

```
/forms/my-survey/en?PROLIFIC_PID=abc123&STUDY_ID=xyz&utm_source=email
```

Only `PROLIFIC_PID` and `STUDY_ID` are captured. `utm_source` is ignored.

## Completion redirect

Instead of showing the default thank-you screen, you can redirect respondents to an external URL after submission.

### Configuration

On the Form edit page, open the **Completion** tab:

- **Redirect URL**: the destination URL
- **Pass query params**: toggle to append captured query params to the redirect URL

### Example

With redirect URL `https://app.prolific.co/submissions/complete?cc=ABC123` and passthrough enabled, a respondent who arrived with `?PROLIFIC_PID=abc123` would be redirected to:

```
https://app.prolific.co/submissions/complete?cc=ABC123&PROLIFIC_PID=abc123
```

## Preview mode

The **Preview** button on the Form edit page generates a signed URL that opens the fill runtime in a new tab. In preview mode:

- Unpublished forms are accessible
- No `Submission` record is created
- A yellow banner indicates preview mode
- The signed URL cannot be shared (signature is tied to the URL and expires with the app key)

## Section-aware progress

When a form has multiple sections, a **"Section X of Y"** indicator appears above the progress bar. For single-section forms, only the progress bar is shown.

Progress is calculated as a percentage of steps completed, not fields answered.
