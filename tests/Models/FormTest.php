<?php

use BlackpigCreatif\Confessionnal\Enums\FormMode;
use BlackpigCreatif\Confessionnal\Models\Form;
use BlackpigCreatif\Confessionnal\Models\Section;
use BlackpigCreatif\Confessionnal\Models\Submission;
use Illuminate\Database\QueryException;

it('can create a form', function () {
    $form = Form::factory()->create([
        'name' => 'Test Survey',
        'slug' => 'test-survey',
        'mode' => FormMode::CONVERSATIONAL,
    ]);

    expect($form)
        ->toBeInstanceOf(Form::class)
        ->name->toBe('Test Survey')
        ->slug->toBe('test-survey')
        ->mode->toBe(FormMode::CONVERSATIONAL);
});

it('casts mode to FormMode enum', function () {
    $form = Form::factory()->create(['mode' => FormMode::STANDARD]);

    expect($form->mode)->toBe(FormMode::STANDARD);
});

it('has sections relationship', function () {
    $form = Form::factory()->create();
    Section::factory()->count(3)->create(['form_id' => $form->id]);

    expect($form->sections)->toHaveCount(3);
});

it('orders sections by sort_order', function () {
    $form = Form::factory()->create();
    Section::factory()->create(['form_id' => $form->id, 'sort_order' => 2]);
    Section::factory()->create(['form_id' => $form->id, 'sort_order' => 0]);
    Section::factory()->create(['form_id' => $form->id, 'sort_order' => 1]);

    expect($form->sections->pluck('sort_order')->all())->toBe([0, 1, 2]);
});

it('has submissions relationship', function () {
    $form = Form::factory()->create();
    Submission::factory()->count(2)->create(['form_id' => $form->id]);

    expect($form->submissions)->toHaveCount(2);
});

it('has translatable name and description', function () {
    $form = Form::factory()->create([
        'name' => ['en' => 'Survey', 'fr' => 'Sondage'],
        'description' => ['en' => 'A test survey', 'fr' => 'Un sondage test'],
    ]);

    app()->setLocale('en');
    expect($form->name)->toBe('Survey');

    app()->setLocale('fr');
    expect($form->name)->toBe('Sondage');
});

it('can be published', function () {
    $form = Form::factory()->published()->create();

    expect($form)
        ->is_published->toBeTrue()
        ->published_at->not->toBeNull();
});

it('enforces unique slugs', function () {
    Form::factory()->create(['slug' => 'unique-slug']);

    Form::factory()->create(['slug' => 'unique-slug']);
})->throws(QueryException::class);
