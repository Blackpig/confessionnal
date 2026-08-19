<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>

    @livewireStyles

    @include('confessionnal::partials.styles')

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: var(--cfnl-font-family);
            background-color: var(--cfnl-bg);
            color: var(--cfnl-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .confessionnal-container {
            flex: 1;
        }
    </style>
</head>
<body>
    {{ $slot }}

    @livewireScripts
</body>
</html>
