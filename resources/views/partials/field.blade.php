@php
    $key = $field['key'];
    $type = $field['type'];
    $errorKey = "answers.{$key}";
    $errors = $stepErrors[$errorKey] ?? [];
@endphp

<div class="confessionnal-field">
    <label for="field-{{ $key }}">
        {{ $field['label'] }}
        @if($field['is_required'])
            <span class="required-mark">*</span>
        @endif
    </label>

    @if($field['help_text'])
        <div class="help-text">{{ $field['help_text'] }}</div>
    @endif

    @switch($type)
        @case('text')
            <input
                type="text"
                id="field-{{ $key }}"
                wire:model="answers.{{ $key }}"
                placeholder="{{ $field['placeholder'] ?? '' }}"
                @if($isConversational) autofocus @endif
            >
            @break

        @case('textarea')
            <textarea
                id="field-{{ $key }}"
                wire:model="answers.{{ $key }}"
                placeholder="{{ $field['placeholder'] ?? '' }}"
                rows="4"
                @if($isConversational) autofocus @endif
            ></textarea>
            @break

        @case('select')
            <select
                id="field-{{ $key }}"
                wire:model="answers.{{ $key }}"
            >
                <option value="">{{ $field['placeholder'] ?? '-- Select --' }}</option>
                @foreach($field['options'] as $option)
                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                @endforeach
            </select>
            @break

        @case('radio')
            <div class="confessionnal-options">
                @foreach($field['options'] as $option)
                    <label>
                        <input
                            type="radio"
                            wire:model="answers.{{ $key }}"
                            value="{{ $option['value'] }}"
                        >
                        {{ $option['label'] }}
                    </label>
                @endforeach
            </div>
            @break

        @case('checkbox')
            <div class="confessionnal-options">
                @foreach($field['options'] as $option)
                    <label>
                        <input
                            type="checkbox"
                            wire:model="answers.{{ $key }}"
                            value="{{ $option['value'] }}"
                        >
                        {{ $option['label'] }}
                    </label>
                @endforeach
            </div>
            @break

        @case('scale')
            @php
                $opts = $field['options'] ?? [];
                $min = (int) ($opts['min'] ?? 1);
                $max = (int) ($opts['max'] ?? 5);
                $labelLow = $opts['label_low'] ?? null;
                $labelMid = $opts['label_mid'] ?? null;
                $labelHigh = $opts['label_high'] ?? null;
                $midValue = (int) round(($min + $max) / 2);
                $scaleValues = range($min, $max);
            @endphp
            <div class="confessionnal-scale-wrapper" x-data="{ selected: $wire.entangle('answers.{{ $key }}') }">
                <div class="confessionnal-scale">
                    @foreach($scaleValues as $val)
                        <label :class="{ 'selected': selected === '{{ $val }}' }">
                            <input
                                type="radio"
                                wire:model="answers.{{ $key }}"
                                value="{{ $val }}"
                                x-on:change="selected = '{{ $val }}'"
                            >
                            {{ $val }}
                        </label>
                    @endforeach
                </div>
                @if($labelLow || $labelMid || $labelHigh)
                    <div class="confessionnal-scale-labels">
                        <span>{{ $labelLow }}</span>
                        <span>{{ $labelMid }}</span>
                        <span>{{ $labelHigh }}</span>
                    </div>
                @endif
            </div>
            @break

        @case('date')
            <input
                type="date"
                id="field-{{ $key }}"
                wire:model="answers.{{ $key }}"
                @if($isConversational) autofocus @endif
            >
            @break

        @case('file_upload')
            <div class="confessionnal-file-upload" x-data="{ filename: null }">
                <label for="field-{{ $key }}" class="confessionnal-file-label">
                    <span x-text="filename ?? 'Choose a file...'"></span>
                </label>
                <input
                    type="file"
                    id="field-{{ $key }}"
                    wire:model="answers.{{ $key }}"
                    x-on:change="filename = $event.target.files[0]?.name"
                >
            </div>
            @break
    @endswitch

    @foreach($errors as $error)
        <div class="error">{{ $error }}</div>
    @endforeach
</div>
