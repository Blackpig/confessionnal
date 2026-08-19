<?php

namespace BlackpigCreatif\Confessionnal\Models;

use BlackpigCreatif\Confessionnal\Enums\FormMode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

class Form extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $table = 'confessionnal_forms';

    protected $guarded = [];

    public array $translatable = ['name', 'description'];

    protected function casts(): array
    {
        return [
            'mode' => FormMode::class,
            'settings' => 'array',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('sort_order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(FormAnalytic::class);
    }

    public function pageAnalytics(): HasMany
    {
        return $this->hasMany(PageAnalytic::class);
    }

    public function analyticsSummary(?Carbon $from = null, ?Carbon $to = null): array
    {
        $query = $this->analytics();

        if ($from) {
            $query->where('date', '>=', $from);
        }
        if ($to) {
            $query->where('date', '<=', $to);
        }

        $totals = $query->selectRaw('SUM(views) as views, SUM(starts) as starts, SUM(completions) as completions')->first();

        $views = (int) ($totals->views ?? 0);
        $starts = (int) ($totals->starts ?? 0);
        $completions = (int) ($totals->completions ?? 0);

        return [
            'views' => $views,
            'starts' => $starts,
            'completions' => $completions,
            'start_rate' => $views > 0 ? round(($starts / $views) * 100, 1) : 0,
            'completion_rate' => $starts > 0 ? round(($completions / $starts) * 100, 1) : 0,
        ];
    }
}
