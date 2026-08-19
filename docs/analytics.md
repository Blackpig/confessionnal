# Analytics

Confessionnal tracks form-level and page-level metrics using simple daily aggregate tables. No Redis, no external services, no beacon infrastructure.

## What is tracked

### Form-level (daily)

| Metric | When recorded |
|---|---|
| **Views** | Each time the fill runtime mounts (page load) |
| **Starts** | The first time a respondent advances past the initial step (once per session) |
| **Completions** | Each successful submission |

### Page-level (daily)

| Metric | When recorded |
|---|---|
| **Page views** | Each time a question page is displayed (intro steps are excluded) |

Page-level views let you calculate drop-off: how many respondents saw page N vs page N+1.

## Where to see it

The form edit page in Filament shows a stats overview widget with three cards:

- **Views** (total form loads)
- **Starts** (total first-step advances, with percentage of views)
- **Completions** (total submissions, with percentage of starts)

## Preview mode

Analytics are not recorded in preview mode. Only real respondent visits are counted.

## Database tables

Two tables are created by the package migrations:

### `confessionnal_form_analytics`

| Column | Type | Description |
|---|---|---|
| `form_id` | FK | The form being tracked |
| `date` | date | Aggregation date |
| `views` | unsigned int | Page loads that day |
| `starts` | unsigned int | First-step advances that day |
| `completions` | unsigned int | Submissions that day |

Unique on `(form_id, date)`.

### `confessionnal_page_analytics`

| Column | Type | Description |
|---|---|---|
| `form_id` | FK | The form being tracked |
| `form_page_id` | FK | The specific page |
| `date` | date | Aggregation date |
| `views` | unsigned int | Times this page was shown that day |

Unique on `(form_id, form_page_id, date)`.

## Querying analytics in code

### Summary for a form

```php
use BlackpigCreatif\Confessionnal\Models\Form;

$form = Form::find(1);

// All-time summary
$summary = $form->analyticsSummary();
// Returns: ['views' => 500, 'starts' => 400, 'completions' => 200, 'start_rate' => 80.0, 'completion_rate' => 50.0]

// Date-filtered summary
$summary = $form->analyticsSummary(
    from: now()->subDays(7),
    to: now(),
);
```

### Daily breakdown

```php
use BlackpigCreatif\Confessionnal\Models\FormAnalytic;

$dailyStats = FormAnalytic::where('form_id', $form->id)
    ->where('date', '>=', now()->subDays(30))
    ->orderBy('date')
    ->get();
```

### Drop-off analysis

```php
use BlackpigCreatif\Confessionnal\Models\PageAnalytic;

$pageViews = PageAnalytic::where('form_id', $form->id)
    ->where('date', '>=', now()->subDays(30))
    ->selectRaw('form_page_id, SUM(views) as total_views')
    ->groupBy('form_page_id')
    ->orderByDesc('total_views')
    ->get();
```

To see ordered drop-off, join with `confessionnal_form_pages` and sort by `sort_order` within each section.

## Models

- `BlackpigCreatif\Confessionnal\Models\FormAnalytic` belongs to `Form`
- `BlackpigCreatif\Confessionnal\Models\PageAnalytic` belongs to `Form` and `FormPage`
- `Form` has `analytics()` and `pageAnalytics()` relationships
