<?php

namespace BlackpigCreatif\Confessionnal\Database\Factories;

use BlackpigCreatif\Confessionnal\Enums\FieldType;
use BlackpigCreatif\Confessionnal\Models\FormField;
use BlackpigCreatif\Confessionnal\Models\FormPage;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormFieldFactory extends Factory
{
    protected $model = FormField::class;

    public function definition(): array
    {
        return [
            'form_page_id' => FormPage::factory(),
            'type' => FieldType::TEXT,
            'key' => fake()->unique()->slug(2),
            'label' => fake()->sentence(3),
            'is_required' => false,
            'sort_order' => 0,
        ];
    }
}
