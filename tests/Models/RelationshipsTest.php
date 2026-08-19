<?php

use BlackpigCreatif\Confessionnal\Enums\FieldType;
use BlackpigCreatif\Confessionnal\Models\Form;
use BlackpigCreatif\Confessionnal\Models\FormField;
use BlackpigCreatif\Confessionnal\Models\FormPage;
use BlackpigCreatif\Confessionnal\Models\Section;
use BlackpigCreatif\Confessionnal\Models\Submission;

it('cascades form deletion to sections', function () {
    $form = Form::factory()->create();
    Section::factory()->count(2)->create(['form_id' => $form->id]);

    $form->delete();

    expect(Section::count())->toBe(0);
});

it('cascades section deletion to pages', function () {
    $section = Section::factory()->create();
    FormPage::factory()->count(2)->create(['section_id' => $section->id]);

    $section->delete();

    expect(FormPage::count())->toBe(0);
});

it('cascades page deletion to fields', function () {
    $page = FormPage::factory()->create();
    FormField::factory()->count(3)->create(['form_page_id' => $page->id]);

    $page->delete();

    expect(FormField::count())->toBe(0);
});

it('builds full form hierarchy', function () {
    $form = Form::factory()->create();
    $section = Section::factory()->create(['form_id' => $form->id]);
    $page = FormPage::factory()->create(['section_id' => $section->id]);
    $field = FormField::factory()->create([
        'form_page_id' => $page->id,
        'type' => FieldType::TEXT,
        'key' => 'first_name',
        'label' => 'First Name',
    ]);

    $loaded = Form::with('sections.pages.fields')->find($form->id);

    expect($loaded->sections)->toHaveCount(1)
        ->and($loaded->sections->first()->pages)->toHaveCount(1)
        ->and($loaded->sections->first()->pages->first()->fields)->toHaveCount(1)
        ->and($loaded->sections->first()->pages->first()->fields->first()->key)->toBe('first_name');
});

it('stores submission answers as json', function () {
    $form = Form::factory()->create();
    $submission = Submission::factory()->create([
        'form_id' => $form->id,
        'answers' => ['first_name' => 'Stuart', 'email' => 'stuart@example.com'],
        'locale' => 'en',
    ]);

    $submission->refresh();

    expect($submission->answers)->toBe(['first_name' => 'Stuart', 'email' => 'stuart@example.com'])
        ->and($submission->locale)->toBe('en');
});

it('stores submission meta as json', function () {
    $submission = Submission::factory()->create([
        'meta' => ['ip' => '127.0.0.1', 'utm_source' => 'prolific'],
    ]);

    $submission->refresh();

    expect($submission->meta)->toBe(['ip' => '127.0.0.1', 'utm_source' => 'prolific']);
});

it('tracks completed submissions', function () {
    $submission = Submission::factory()->completed()->create();

    expect($submission->completed_at)->not->toBeNull();
});
