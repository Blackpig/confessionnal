<?php

namespace BlackpigCreatif\Confessionnal\Filament\Resources\FormResource\Widgets;

use BlackpigCreatif\Confessionnal\Models\Form;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FormAnalyticsOverview extends BaseWidget
{
    public ?Form $record = null;

    protected function getStats(): array
    {
        if (! $this->record) {
            return [];
        }

        $summary = $this->record->analyticsSummary();

        return [
            Stat::make('Views', number_format($summary['views']))
                ->description('Total form loads')
                ->icon('heroicon-o-eye'),
            Stat::make('Starts', number_format($summary['starts']))
                ->description($summary['start_rate'] . '% of views')
                ->icon('heroicon-o-play'),
            Stat::make('Completions', number_format($summary['completions']))
                ->description($summary['completion_rate'] . '% of starts')
                ->icon('heroicon-o-check-circle'),
        ];
    }
}
