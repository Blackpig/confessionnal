<?php

namespace BlackpigCreatif\Confessionnal\Models;

use BlackpigCreatif\ChambreNoir\Concerns\HasRetouchMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormPage extends Model
{
    use HasFactory;
    use HasRetouchMedia;

    protected $table = 'confessionnal_form_pages';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'context_image' => 'array',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('sort_order');
    }
}
