<?php

namespace BlackpigCreatif\Confessionnal\Database\Factories;

use BlackpigCreatif\Confessionnal\Models\Form;
use BlackpigCreatif\Confessionnal\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

class SectionFactory extends Factory
{
    protected $model = Section::class;

    public function definition(): array
    {
        return [
            'form_id' => Form::factory(),
            'title' => fake()->sentence(3),
            'subtext' => fake()->sentence(),
            'sort_order' => 0,
        ];
    }
}
