<?php

use BlackpigCreatif\Confessionnal\Enums\FieldType;
use BlackpigCreatif\Confessionnal\Models\FormField;

it('casts type to FieldType enum', function () {
    $field = FormField::factory()->create(['type' => FieldType::SELECT]);

    expect($field->type)->toBe(FieldType::SELECT);
});

it('stores validation rules as array', function () {
    $field = FormField::factory()->create([
        'validation_rules' => ['required', 'email', 'max:255'],
    ]);

    $field->refresh();

    expect($field->validation_rules)->toBe(['required', 'email', 'max:255']);
});

it('stores options as array', function () {
    $field = FormField::factory()->create([
        'type' => FieldType::SELECT,
        'options' => [
            ['label' => 'Option A', 'value' => 'a'],
            ['label' => 'Option B', 'value' => 'b'],
        ],
    ]);

    $field->refresh();

    expect($field->options)
        ->toHaveCount(2)
        ->and($field->options[0]['label'])->toBe('Option A');
});

it('has translatable label and help text', function () {
    $field = FormField::factory()->create([
        'label' => ['en' => 'Your Name', 'fr' => 'Votre Nom'],
        'help_text' => ['en' => 'Enter your full name', 'fr' => 'Entrez votre nom complet'],
    ]);

    app()->setLocale('en');
    expect($field->label)->toBe('Your Name')
        ->and($field->help_text)->toBe('Enter your full name');

    app()->setLocale('fr');
    expect($field->label)->toBe('Votre Nom');
});
