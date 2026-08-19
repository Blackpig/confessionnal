<div class="space-y-4 p-4">
    @foreach($sections as $section)
        @if($sections->count() > 1)
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 border-b pb-1">
                {{ $section->getTranslation('title', app()->getLocale()) ?: '(untitled section)' }}
            </h3>
        @endif

        <dl class="space-y-3">
            @foreach($section->pages as $page)
                @foreach($page->fields as $field)
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
            @endforeach
        </dl>
    @endforeach

    <div class="border-t pt-3 mt-4 space-y-1 text-xs text-gray-500 dark:text-gray-400">
        <div><strong>Locale:</strong> {{ $submission->locale }}</div>
        <div><strong>Completed:</strong> {{ $submission->completed_at?->toDateTimeString() ?? 'In progress' }}</div>
        <div><strong>Submitted:</strong> {{ $submission->created_at->toDateTimeString() }}</div>

        @if($submission->meta)
            <div><strong>IP:</strong> {{ $submission->meta['ip'] ?? '--' }}</div>
            <div><strong>Referrer:</strong> {{ $submission->meta['referrer'] ?? '--' }}</div>

            @if(! empty($submission->meta['query_params']))
                <div class="mt-2">
                    <strong>Query params:</strong>
                    @foreach($submission->meta['query_params'] as $key => $value)
                        <span class="inline-block bg-gray-100 dark:bg-gray-700 rounded px-1.5 py-0.5 mr-1">{{ $key }}={{ $value }}</span>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</div>
