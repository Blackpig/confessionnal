<?php

namespace BlackpigCreatif\Confessionnal\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Section extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $table = 'confessionnal_sections';

    protected $guarded = [];

    public array $translatable = ['title', 'subtext'];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(FormPage::class)->orderBy('sort_order');
    }
}
