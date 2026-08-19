<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>

    @livewireStyles

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .confessionnal-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .confessionnal-card {
            width: 100%;
            max-width: 640px;
        }

        .confessionnal-progress {
            width: 100%;
            max-width: 640px;
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .confessionnal-progress-bar {
            height: 100%;
            background: #3b82f6;
            border-radius: 2px;
            transition: width 0.3s ease;
        }

        .confessionnal-intro { text-align: center; }
        .confessionnal-intro h2 { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.75rem; }
        .confessionnal-intro p { color: #64748b; font-size: 1.1rem; line-height: 1.6; margin-bottom: 2rem; }

        .confessionnal-field { margin-bottom: 1.5rem; }
        .confessionnal-field label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }
        .confessionnal-field .help-text {
            color: #64748b;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }
        .confessionnal-field .required-mark { color: #ef4444; }

        .confessionnal-field input[type="text"],
        .confessionnal-field input[type="date"],
        .confessionnal-field textarea,
        .confessionnal-field select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            font-size: 1rem;
            background: white;
            transition: border-color 0.15s ease;
        }
        .confessionnal-field input:focus,
        .confessionnal-field textarea:focus,
        .confessionnal-field select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .confessionnal-field textarea { resize: vertical; min-height: 120px; }

        .confessionnal-field .error {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .confessionnal-options label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            cursor: pointer;
            font-weight: 400;
            font-size: 1rem;
            transition: border-color 0.15s ease, background 0.15s ease;
        }
        .confessionnal-options label:hover {
            border-color: #3b82f6;
            background: #f0f7ff;
        }
        .confessionnal-options input[type="radio"],
        .confessionnal-options input[type="checkbox"] {
            width: 1.125rem;
            height: 1.125rem;
            accent-color: #3b82f6;
        }

        .confessionnal-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            gap: 1rem;
        }

        .confessionnal-btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: background 0.15s ease, transform 0.1s ease;
        }
        .confessionnal-btn:active { transform: scale(0.98); }

        .confessionnal-btn-primary {
            background: #3b82f6;
            color: white;
        }
        .confessionnal-btn-primary:hover { background: #2563eb; }

        .confessionnal-btn-secondary {
            background: #e2e8f0;
            color: #475569;
        }
        .confessionnal-btn-secondary:hover { background: #cbd5e1; }

        .confessionnal-btn-ghost {
            background: transparent;
            color: #64748b;
        }

        .confessionnal-enter-hint {
            color: #94a3b8;
            font-size: 0.8rem;
        }

        .confessionnal-complete {
            text-align: center;
            padding: 3rem 0;
        }
        .confessionnal-complete h2 { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.75rem; }
        .confessionnal-complete p { color: #64748b; font-size: 1.1rem; }

        .confessionnal-scale {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .confessionnal-scale label {
            flex: 1;
            min-width: 3rem;
            text-align: center;
            padding: 0.75rem 0.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            cursor: pointer;
            font-weight: 400;
            font-size: 1rem;
            transition: all 0.15s ease;
        }
        .confessionnal-scale label:hover { border-color: #3b82f6; background: #f0f7ff; }
        .confessionnal-scale label.selected { border-color: #3b82f6; background: #3b82f6; color: white; }
        .confessionnal-scale input { display: none; }

        .confessionnal-scale-labels {
            display: flex;
            justify-content: space-between;
            margin-top: 0.5rem;
            font-size: 0.8rem;
            color: #64748b;
        }
        .confessionnal-scale-labels span {
            flex: 1;
            text-align: center;
        }
        .confessionnal-scale-labels span:first-child { text-align: left; }
        .confessionnal-scale-labels span:last-child { text-align: right; }

        .confessionnal-file-upload { position: relative; }
        .confessionnal-file-upload input[type="file"] {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px dashed #cbd5e1;
            border-radius: 0.5rem;
            font-size: 1rem;
            background: white;
            cursor: pointer;
            transition: border-color 0.15s ease;
        }
        .confessionnal-file-upload input[type="file"]:hover { border-color: #3b82f6; }
        .confessionnal-file-upload input[type="file"]:focus { outline: none; border-color: #3b82f6; }
        .confessionnal-file-upload .confessionnal-file-label { display: none; }
    </style>
</head>
<body>
    {{ $slot }}

    @livewireScripts
</body>
</html>
