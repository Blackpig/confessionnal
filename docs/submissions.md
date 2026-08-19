# Submissions

## Viewing submissions

Submissions are displayed as a relation manager on the Form edit page. The table shows:

- Submission ID
- Up to 5 answer columns (auto-generated from the form's fields)
- Locale (toggleable, hidden by default)
- Completed at
- Created at (toggleable, hidden by default)

Clicking the **eye icon** opens a detail modal showing all answers grouped by section, plus metadata (locale, timestamps, IP, referrer, query params).

## Data structure

Each submission stores:

| Column | Type | Description |
|---|---|---|
| `form_id` | FK | The form this submission belongs to |
| `answers` | JSON | Flat key/value map of all field answers |
| `locale` | string | Locale the form was completed in |
| `section_order` | JSON | Array of section IDs in the order shown (relevant for randomised sections) |
| `meta` | JSON | IP address, user agent, referrer, captured query params |
| `completed_at` | datetime | When the form was submitted (null if abandoned) |

### Answer format

Answers are stored as a flat JSON object keyed by field key:

```json
{
    "full_name": "Alice",
    "colours": ["red", "blue"],
    "rating": "4",
    "start_date": "2025-03-15"
}
```

Array values (checkboxes) are stored as JSON arrays. Scale values are stored as strings.

## CSV export

The Submissions table has an **Export CSV** header action. The export:

- Includes all fields as columns, plus ID, Locale, Completed At, and Created At
- Array values are joined with commas (e.g. `red, blue`)
- Missing answers are exported as empty strings
- Prefixes column headers with section titles when duplicate labels exist across sections (e.g. "Brand A - Rating", "Brand B - Rating")
- Names the file `{slug}-submissions-{date}.csv`

### Programmatic export

```php
use BlackpigCreatif\Confessionnal\Exports\SubmissionCsvExport;
use BlackpigCreatif\Confessionnal\Models\Form;

$form = Form::find(1);
return SubmissionCsvExport::download($form);
```
