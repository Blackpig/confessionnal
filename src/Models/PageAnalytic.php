<?php

namespace BlackpigCreatif\Confessionnal\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageAnalytic extends Model
{
    protected $table = 'confessionnal_page_analytics';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'views' => 'integer',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(FormPage::class, 'form_page_id');
    }
}
