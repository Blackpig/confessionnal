<?php

namespace BlackpigCreatif\Confessionnal\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    use HasFactory;

    protected $table = 'confessionnal_submissions';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'section_order' => 'array',
            'meta' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}
