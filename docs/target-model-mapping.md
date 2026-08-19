# Target Model Mapping

By default, form submissions are stored as generic JSON in the `confessionnal_submissions` table. Target model mapping lets a form also write into a real Eloquent model on submit, so an intake form can create a `Client` or `Booking` record directly.

## Setting up a target model

### 1. Implement the contract

Your model must implement `CanReceiveSubmissions`. The `HasSubmissionMapping` trait provides sensible defaults:

```php
use BlackpigCreatif\Confessionnal\Contracts\CanReceiveSubmissions;
use BlackpigCreatif\Confessionnal\Concerns\HasSubmissionMapping;
use Illuminate\Database\Eloquent\Model;

class Client extends Model implements CanReceiveSubmissions
{
    use HasSubmissionMapping;

    // Optional: explicitly list mappable columns.
    // Without this, all columns except id, timestamps, password, etc. are auto-derived.
    protected array $mappable = ['name', 'email', 'phone'];
}
```

### 2. Register the model (if needed)

Models in `app/Models/` that implement `CanReceiveSubmissions` are auto-discovered. For models in other namespaces, add them to `config/confessionnal.php`:

```php
'mappable_models' => [
    \App\Domain\Clients\Client::class,
],
```

### 3. Configure the mapping in the admin panel

On the Form edit page, open the **Target Model** tab:

- **Model class**: select the target model
- **Write mode**: `Create new record` or `Update or create (upsert)`
- **Find by column** (upsert only): the column to match on
- **Field mapping**: map form field keys to model columns

## How it works

On submit, after the generic `Submission` is saved:

1. The mapper reads the field mapping config from the form's settings
2. Each mapped field key's answer is extracted and assigned to the corresponding model column
3. Array values (e.g. checkboxes) are flattened to comma-separated strings
4. The model record is created (or upserted if configured)

If the mapper throws for any reason (missing column, constraint violation, bad class), the error is reported to the exception handler but the submission still succeeds. The respondent always sees the thank-you screen.

## The `CanReceiveSubmissions` contract

```php
interface CanReceiveSubmissions
{
    public static function getMappingName(): string;
    public static function getMappableColumns(): array;
}
```

- `getMappingName()`: display label for the model in the admin dropdown (defaults to class basename)
- `getMappableColumns()`: returns `['column_name' => 'Display Label']` pairs. The trait auto-derives this from the database schema, excluding `id`, timestamps, `password`, and `remember_token`. Override the `$mappable` property for explicit control.
