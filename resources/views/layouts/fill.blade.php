<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>

    @livewireStyles

    <style>
        :root {
            --cfnl-font-family: system-ui, -apple-system, sans-serif;
            --cfnl-max-width: 640px;

            /* Colours */
            --cfnl-bg: #f8fafc;
            --cfnl-color: #1e293b;
            --cfnl-color-muted: #64748b;
            --cfnl-color-hint: #94a3b8;
            --cfnl-primary: #3b82f6;
            --cfnl-primary-hover: #2563eb;
            --cfnl-primary-light: #f0f7ff;
            --cfnl-primary-ring: rgba(59, 130, 246, 0.1);
            --cfnl-error: #ef4444;

            /* Inputs */
            --cfnl-input-bg: white;
            --cfnl-input-border: #cbd5e1;
            --cfnl-input-radius: 0.5rem;

            /* Options (radio/checkbox/scale cards) */
            --cfnl-option-border: #e2e8f0;

            /* Buttons */
            --cfnl-btn-radius: 0.5rem;
            --cfnl-btn-secondary-bg: #e2e8f0;
            --cfnl-btn-secondary-color: #475569;
            --cfnl-btn-secondary-hover: #cbd5e1;

            /* Progress bar */
            --cfnl-progress-bg: #e2e8f0;
            --cfnl-progress-fill: var(--cfnl-primary);
        }

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
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .confessionnal-card {
            width: 100%;
            max-width: var(--cfnl-max-width);
        }

        .confessionnal-progress-wrapper {
            width: 100%;
            max-width: var(--cfnl-max-width);
            margin-bottom: 2rem;
        }

        .confessionnal-section-indicator {
            font-size: 0.8rem;
            color: var(--cfnl-color-hint);
            text-align: right;
            margin-bottom: 0.375rem;
        }

        .confessionnal-progress {
            width: 100%;
            height: 4px;
            background: var(--cfnl-progress-bg);
            border-radius: 2px;
            overflow: hidden;
        }

        .confessionnal-progress-bar {
            height: 100%;
            background: var(--cfnl-progress-fill);
            border-radius: 2px;
            transition: width 0.3s ease;
        }

        .confessionnal-intro { text-align: center; }
        .confessionnal-intro h2 { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.75rem; }
        .confessionnal-intro p { color: var(--cfnl-color-muted); font-size: 1.1rem; line-height: 1.6; margin-bottom: 2rem; }

        .confessionnal-context-image {
            margin-bottom: 1.5rem;
            border-radius: var(--cfnl-input-radius);
            overflow: hidden;
        }
        .confessionnal-context-image img {
            width: 100%;
            height: auto;
            display: block;
        }

        .confessionnal-field { margin-bottom: 1.5rem; }
        .confessionnal-field label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }
        .confessionnal-field .help-text {
            color: var(--cfnl-color-muted);
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }
        .confessionnal-field .required-mark { color: var(--cfnl-error); }

        .confessionnal-field input[type="text"],
        .confessionnal-field input[type="date"],
        .confessionnal-field textarea,
        .confessionnal-field select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--cfnl-input-border);
            border-radius: var(--cfnl-input-radius);
            font-size: 1rem;
            background: var(--cfnl-input-bg);
            transition: border-color 0.15s ease;
        }
        .confessionnal-field input:focus,
        .confessionnal-field textarea:focus,
        .confessionnal-field select:focus {
            outline: none;
            border-color: var(--cfnl-primary);
            box-shadow: 0 0 0 3px var(--cfnl-primary-ring);
        }
        .confessionnal-field textarea { resize: vertical; min-height: 120px; }

        .confessionnal-field .error {
            color: var(--cfnl-error);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .confessionnal-options label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border: 1px solid var(--cfnl-option-border);
            border-radius: var(--cfnl-input-radius);
            margin-bottom: 0.5rem;
            cursor: pointer;
            font-weight: 400;
            font-size: 1rem;
            transition: border-color 0.15s ease, background 0.15s ease;
        }
        .confessionnal-options label:hover {
            border-color: var(--cfnl-primary);
            background: var(--cfnl-primary-light);
        }
        .confessionnal-options input[type="radio"],
        .confessionnal-options input[type="checkbox"] {
            width: 1.125rem;
            height: 1.125rem;
            accent-color: var(--cfnl-primary);
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
            border-radius: var(--cfnl-btn-radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: background 0.15s ease, transform 0.1s ease;
        }
        .confessionnal-btn:active { transform: scale(0.98); }

        .confessionnal-btn-primary {
            background: var(--cfnl-primary);
            color: white;
        }
        .confessionnal-btn-primary:hover { background: var(--cfnl-primary-hover); }

        .confessionnal-btn-secondary {
            background: var(--cfnl-btn-secondary-bg);
            color: var(--cfnl-btn-secondary-color);
        }
        .confessionnal-btn-secondary:hover { background: var(--cfnl-btn-secondary-hover); }

        .confessionnal-btn-ghost {
            background: transparent;
            color: var(--cfnl-color-muted);
        }

        .confessionnal-enter-hint {
            color: var(--cfnl-color-hint);
            font-size: 0.8rem;
        }

        .confessionnal-complete {
            text-align: center;
            padding: 3rem 0;
        }
        .confessionnal-complete h2 { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.75rem; }
        .confessionnal-complete p { color: var(--cfnl-color-muted); font-size: 1.1rem; }

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
            border: 1px solid var(--cfnl-option-border);
            border-radius: var(--cfnl-input-radius);
            cursor: pointer;
            font-weight: 400;
            font-size: 1rem;
            transition: all 0.15s ease;
        }
        .confessionnal-scale label:hover { border-color: var(--cfnl-primary); background: var(--cfnl-primary-light); }
        .confessionnal-scale label.selected { border-color: var(--cfnl-primary); background: var(--cfnl-primary); color: white; }
        .confessionnal-scale input { display: none; }

        .confessionnal-scale-labels {
            display: flex;
            justify-content: space-between;
            margin-top: 0.5rem;
            font-size: 0.8rem;
            color: var(--cfnl-color-muted);
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
            border: 2px dashed var(--cfnl-input-border);
            border-radius: var(--cfnl-input-radius);
            font-size: 1rem;
            background: var(--cfnl-input-bg);
            cursor: pointer;
            transition: border-color 0.15s ease;
        }
        .confessionnal-file-upload input[type="file"]:hover { border-color: var(--cfnl-primary); }
        .confessionnal-file-upload input[type="file"]:focus { outline: none; border-color: var(--cfnl-primary); }
        .confessionnal-file-upload .confessionnal-file-label { display: none; }

        .confessionnal-preview-banner {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #f59e0b;
            color: #78350f;
            text-align: center;
            padding: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            z-index: 50;
        }
        .confessionnal-container.has-preview-banner {
            padding-top: 4rem;
        }

        /* Step transition */
        @keyframes confessionnal-fade-up {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .confessionnal-step-transition {
            animation: confessionnal-fade-up 0.25s ease-out;
        }

        /* Completion fade-in */
        @keyframes confessionnal-fade-in {
            from { opacity: 0; transform: scale(0.96); }
            to { opacity: 1; transform: scale(1); }
        }
        .confessionnal-fade-in {
            animation: confessionnal-fade-in 0.4s ease-out;
        }

        /* Disabled button state */
        .confessionnal-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    {{ $slot }}

    @livewireScripts
</body>
</html>
