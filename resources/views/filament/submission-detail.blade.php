<div class="space-y-4 p-4">
    <dl class="space-y-3">
        @foreach($fields as $field)
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ $field->getTranslation('label', app()->getLocale()) }}
                </dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                    @php
                        $value = $submission->answers[$field->key] ?? null;
                    @endphp

                    @if(is_array($value))
                        {{ implode(', ', $value) }}
                    @elseif($value)
                        {{ $value }}
                    @else
                        <span class="text-gray-400 italic">--</span>
                    @endif
                </dd>
            </div>
        @endforeach
    </dl>

    <div class="border-t pt-3 mt-4 space-y-1 text-xs text-gray-500 dark:text-gray-400">
        <div><strong>Locale:</strong> {{ $submission->locale }}</div>
        <div><strong>Completed:</strong> {{ $submission->completed_at?->toDateTimeString() ?? 'In progress' }}</div>
        <div><strong>Submitted:</strong> {{ $submission->created_at->toDateTimeString() }}</div>

        @if($submission->meta)
            <div><strong>IP:</strong> {{ $submission->meta['ip'] ?? '--' }}</div>
            <div><strong>Referrer:</strong> {{ $submission->meta['referrer'] ?? '--' }}</div>
        @endif
    </div>
</div>
