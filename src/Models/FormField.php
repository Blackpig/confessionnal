<?php

namespace BlackpigCreatif\Confessionnal\Models;

use BlackpigCreatif\Confessionnal\Enums\FieldType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class FormField extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $table = 'confessionnal_form_fields';

    protected $guarded = [];

    public array $translatable = ['label', 'help_text', 'placeholder'];

    protected function casts(): array
    {
        return [
            'type' => FieldType::class,
            'validation_rules' => 'array',
            'options' => 'array',
            'conditional_logic' => 'array',
            'is_required' => 'boolean',
        ];
    }

    public function formPage(): BelongsTo
    {
        return $this->belongsTo(FormPage::class);
    }
}
