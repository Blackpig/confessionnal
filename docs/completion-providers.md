# Completion Providers

Completion providers handle the end-of-survey flow for panel-recruitment services like Prolific, Cint, Toluna, and Amazon MTurk. Each provider knows how to detect its respondents via URL query parameters, optionally generate a completion code, and redirect to the correct return URL.

## Built-in providers

| Provider | Detection parameter | Default behaviour |
|---|---|---|
| **Prolific** | `PROLIFIC_PID` | Static completion code appended to redirect URL as `?cc=CODE` |
| **Cint** | `rid` | Redirect with query param passthrough, no code |
| **Toluna** | `respondentid` | Redirect with query param passthrough, no code |
| **MTurk** | `assignmentId` | Dynamic per-submission code displayed on thank-you screen |
| **Generic** | Configurable | Fully configurable detection, redirect, and code behaviour |

## How it works

1. When a respondent loads a form, Confessionnal checks the URL query parameters against each configured provider's detection parameter.
2. The first matching provider is stored for that session.
3. On submission, the matched provider generates a completion code (if configured) and builds a redirect URL.
4. The respondent is either redirected or shown the completion code on the thank-you screen.

Provider detection runs independently of the Query Capture setting. Even with capture mode set to "none", providers are still detected.

## Configuring providers

On the Form edit page, open the **Completion** tab.

### Default redirect

The **Default redirect URL** is used when no provider matches (or no providers are configured). Leave blank to show the built-in thank-you screen.

### Adding a provider

Click **Add** in the Panel providers repeater, then select a provider from the dropdown. Provider-specific configuration fields appear automatically.

Common configuration fields:

| Field | Description |
|---|---|
| **Redirect URL** | Where to send the respondent after submission |
| **Pass captured query params** | Append all captured query params to the redirect URL |
| **Completion code** | `None`, `Static` (same code for all respondents), or `Dynamic` (unique 8-character alphanumeric code per submission) |
| **Code** | The static code value. Click the refresh icon to generate one. |
| **Code URL parameter** | Query parameter name for appending the code to the redirect URL (e.g. `cc`) |

### Multiple providers

You can configure multiple providers on a single form. The first one whose detection parameter is found in the URL wins. This lets you run the same survey across multiple panels simultaneously.

## Completion codes

### Static codes

A single code shared by all respondents. Typical for Prolific, where one code is set per study. The code is appended to the redirect URL as a query parameter.

### Dynamic codes

A unique 8-character alphanumeric code generated per submission. Useful for MTurk-style workflows where each respondent needs a distinct proof of completion. If no redirect URL is set, the code is displayed on the thank-you screen for the respondent to copy.

### Storage

Completion codes are stored on the `completion_code` column of the submissions table. The matched provider name is stored in `meta.provider`.

## Example: Prolific setup

1. Edit your form, go to the **Completion** tab
2. Add a provider, select **Prolific**
3. Set the redirect URL to `https://app.prolific.com/submissions/complete`
4. Set completion code to **Static** and enter (or generate) the code from your Prolific study
5. Set code URL parameter to `cc`
6. Go to the **Query Capture** tab and set capture mode to **All** (or whitelist `PROLIFIC_PID`, `STUDY_ID`, `SESSION_ID`)

When a Prolific respondent arrives at `https://yoursite.com/forms/my-survey/en?PROLIFIC_PID=abc123`, Confessionnal detects the Prolific provider. On submission, the respondent is redirected to:

```
https://app.prolific.com/submissions/complete?cc=YOUR_CODE
```

## Example: Multiple panels

To run a survey on both Prolific and Cint:

1. Add a **Prolific** provider with its redirect URL and static code
2. Add a **Cint** provider with its redirect URL and passthrough enabled
3. Set query capture to **All**

Prolific respondents (identified by `PROLIFIC_PID`) get the Prolific redirect with code. Cint respondents (identified by `rid`) get the Cint redirect with their `rid` passed through. Anyone else sees the default thank-you screen or default redirect URL.

## Custom providers

### Using the Generic provider

For services not covered by the built-in providers, use **Generic**. It lets you specify your own detection parameter, redirect URL, and code configuration.

### Creating a provider class

For reusable provider logic, create a class that implements `CompletionProvider`:

```php
<?php

namespace App\CompletionProviders;

use BlackpigCreatif\Confessionnal\CompletionProviders\BaseCompletionProvider;

class MyPanelProvider extends BaseCompletionProvider
{
    public static function getName(): string
    {
        return 'My Panel';
    }

    public static function getDescription(): ?string
    {
        return 'Detects respondents via the panel_uid parameter.';
    }

    public static function getDetectParams(): array
    {
        return ['panel_uid'];
    }

    public static function getDefaults(): array
    {
        return [
            'redirect_url' => 'https://mypanel.com/complete',
            'passthrough_params' => true,
            'code_type' => 'none',
            'static_code' => '',
            'code_param_key' => '',
        ];
    }

    public static function getConfigSchema(): array
    {
        return [
            static::redirectUrlField('https://mypanel.com/complete'),
            static::passthroughField(default: true),
            ...static::codeFields(),
        ];
    }
}
```

Register it in `config/confessionnal.php`:

```php
'completion_providers' => [
    \App\CompletionProviders\MyPanelProvider::class,
],
```

The provider will appear in the dropdown alongside the built-in options.

### Available base class helpers

`BaseCompletionProvider` provides reusable Filament field builders for config schemas:

| Method | Returns |
|---|---|
| `redirectUrlField(?string $default)` | URL text input |
| `passthroughField(bool $default)` | Toggle for query param passthrough |
| `codeFields(string $defaultType, ?string $defaultParamKey)` | Code type select, static code input (with generate button), and code param key input |

### Overriding detection

Built-in providers use hardcoded detection parameters. If you need custom detection logic (e.g. matching by referrer header or multiple parameters), override the `detect` method:

```php
public function detect(array $queryParams, array $config): bool
{
    return isset($queryParams['panel_uid'])
        && isset($queryParams['study_ref']);
}
```
