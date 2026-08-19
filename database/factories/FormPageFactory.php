<?php

namespace BlackpigCreatif\Confessionnal\Database\Factories;

use BlackpigCreatif\Confessionnal\Models\FormPage;
use BlackpigCreatif\Confessionnal\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormPageFactory extends Factory
{
    protected $model = FormPage::class;

    public function definition(): array
    {
        return [
            'section_id' => Section::factory(),
            'sort_order' => 0,
        ];
    }
}
