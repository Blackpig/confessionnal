<?php

namespace BlackpigCreatif\Confessionnal\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormAnalytic extends Model
{
    protected $table = 'confessionnal_form_analytics';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'views' => 'integer',
            'starts' => 'integer',
            'completions' => 'integer',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}
