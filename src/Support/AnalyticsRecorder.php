<?php

namespace BlackpigCreatif\Confessionnal\Support;

use BlackpigCreatif\Confessionnal\Models\FormAnalytic;
use BlackpigCreatif\Confessionnal\Models\PageAnalytic;
use Illuminate\Support\Carbon;

class AnalyticsRecorder
{
    public static function recordView(int $formId): void
    {
        static::incrementFormMetric($formId, 'views');
    }

    public static function recordStart(int $formId): void
    {
        static::incrementFormMetric($formId, 'starts');
    }

    public static function recordCompletion(int $formId): void
    {
        static::incrementFormMetric($formId, 'completions');
    }

    public static function recordPageView(int $formId, int $formPageId): void
    {
        $today = Carbon::today();

        $row = PageAnalytic::where('form_id', $formId)
            ->where('form_page_id', $formPageId)
            ->whereDate('date', $today)
            ->first();

        if (! $row) {
            $row = PageAnalytic::create([
                'form_id' => $formId,
                'form_page_id' => $formPageId,
                'date' => $today,
                'views' => 0,
            ]);
        }

        $row->increment('views');
    }

    protected static function incrementFormMetric(int $formId, string $column): void
    {
        $today = Carbon::today();

        $row = FormAnalytic::where('form_id', $formId)
            ->whereDate('date', $today)
            ->first();

        if (! $row) {
            $row = FormAnalytic::create([
                'form_id' => $formId,
                'date' => $today,
                'views' => 0,
                'starts' => 0,
                'completions' => 0,
            ]);
        }

        $row->increment($column);
    }
}
