<?php

use BlackpigCreatif\Confessionnal\Enums\FieldType;
use BlackpigCreatif\Confessionnal\Enums\FormMode;
use BlackpigCreatif\Confessionnal\Livewire\FormFill;
use BlackpigCreatif\Confessionnal\Models\Form;
use BlackpigCreatif\Confessionnal\Models\FormField;
use BlackpigCreatif\Confessionnal\Models\FormPage;
use BlackpigCreatif\Confessionnal\Models\Section;
use BlackpigCreatif\Confessionnal\Models\Submission;
use Livewire\Livewire;

function createFormWithProvider(array $providers = [], array $extraSettings = []): Form
{
    $form = Form::create([
        'name' => 'Provider Test',
        'slug' => 'provider-test',
        'mode' => FormMode::STANDARD,
        'is_published' => true,
        'settings' => array_merge([
            'query_capture_mode' => 'all',
            'completion_providers' => $providers,
        ], $extraSettings),
    ]);

    $section = Section::create([
        'form_id' => $form->id,
        'title' => 'Section',
        'sort_order' => 0,
    ]);

    $page = FormPage::create([
        'section_id' => $section->id,
        'sort_order' => 0,
    ]);

    FormField::create([
        'form_page_id' => $page->id,
        'type' => FieldType::TEXT,
        'label' => 'Name',
        'key' => 'name',
        'is_required' => false,
        'sort_order' => 0,
    ]);

    return $form;
}

it('detects a provider by query parameter', function () {
    $form = createFormWithProvider([
        [
            'name' => 'Prolific',
            'detect_param' => 'PROLIFIC_PID',
            'redirect_url' => 'https://app.prolific.com/submissions/complete',
            'passthrough_params' => false,
            'code_type' => 'static',
            'static_code' => 'ABC123',
            'code_param_key' => 'cc',
        ],
    ]);

    $component = Livewire::withQueryParams(['PROLIFIC_PID' => 'participant1'])
        ->test(FormFill::class, ['slug' => 'provider-test']);

    expect($component->get('matchedProvider')['name'])->toBe('Prolific');
});

it('does not match a provider when detect param is absent', function () {
    $form = createFormWithProvider([
        [
            'name' => 'Prolific',
            'detect_param' => 'PROLIFIC_PID',
            'redirect_url' => 'https://app.prolific.com/submissions/complete',
            'passthrough_params' => false,
            'code_type' => 'static',
            'static_code' => 'ABC123',
            'code_param_key' => 'cc',
        ],
    ]);

    $component = Livewire::test(FormFill::class, ['slug' => 'provider-test']);

    expect($component->get('matchedProvider'))->toBeNull();
});

it('uses static completion code and redirects for Prolific-style provider', function () {
    $form = createFormWithProvider([
        [
            'name' => 'Prolific',
            'detect_param' => 'PROLIFIC_PID',
            'redirect_url' => 'https://app.prolific.com/submissions/complete',
            'passthrough_params' => false,
            'code_type' => 'static',
            'static_code' => 'STUDY99',
            'code_param_key' => 'cc',
        ],
    ]);

    $component = Livewire::withQueryParams(['PROLIFIC_PID' => 'p123'])
        ->test(FormFill::class, ['slug' => 'provider-test']);

    // Navigate past intro
    $component->call('next');
    // Submit
    $component->call('next');

    expect($component->get('completed'))->toBeTrue();
    expect($component->get('redirectUrl'))->toBe('https://app.prolific.com/submissions/complete?cc=STUDY99');

    $submission = Submission::latest()->first();
    expect($submission->completion_code)->toBe('STUDY99');
    expect($submission->meta['provider'])->toBe('Prolific');
});

it('generates dynamic completion code per submission', function () {
    $form = createFormWithProvider([
        [
            'name' => 'MTurk',
            'detect_param' => 'assignmentId',
            'redirect_url' => '',
            'passthrough_params' => false,
            'code_type' => 'dynamic',
            'static_code' => '',
            'code_param_key' => '',
        ],
    ]);

    $component = Livewire::withQueryParams(['assignmentId' => 'hit123'])
        ->test(FormFill::class, ['slug' => 'provider-test']);

    $component->call('next');
    $component->call('next');

    expect($component->get('completed'))->toBeTrue();
    expect($component->get('completionCode'))->toBeString()->toHaveLength(8);
    expect($component->get('redirectUrl'))->toBeNull();

    $submission = Submission::latest()->first();
    expect($submission->completion_code)->toBe($component->get('completionCode'));
});

it('passes captured params through to provider redirect URL', function () {
    $form = createFormWithProvider([
        [
            'name' => 'Cint',
            'detect_param' => 'rid',
            'redirect_url' => 'https://s.cint.com/Survey/Complete?ProjectToken=XYZ',
            'passthrough_params' => true,
            'code_type' => 'none',
            'static_code' => '',
            'code_param_key' => '',
        ],
    ]);

    $component = Livewire::withQueryParams(['rid' => 'resp456'])
        ->test(FormFill::class, ['slug' => 'provider-test']);

    $component->call('next');
    $component->call('next');

    expect($component->get('completed'))->toBeTrue();
    expect($component->get('redirectUrl'))->toContain('https://s.cint.com/Survey/Complete?ProjectToken=XYZ');
    expect($component->get('redirectUrl'))->toContain('rid=resp456');
    expect($component->get('completionCode'))->toBeNull();
});

it('falls back to default redirect when no provider matches', function () {
    $form = createFormWithProvider(
        providers: [
            [
                'name' => 'Prolific',
                'detect_param' => 'PROLIFIC_PID',
                'redirect_url' => 'https://app.prolific.com/submissions/complete',
                'passthrough_params' => false,
                'code_type' => 'static',
                'static_code' => 'ABC',
                'code_param_key' => 'cc',
            ],
        ],
        extraSettings: [
            'redirect_url' => 'https://example.com/thanks',
        ],
    );

    $component = Livewire::test(FormFill::class, ['slug' => 'provider-test']);

    $component->call('next');
    $component->call('next');

    expect($component->get('completed'))->toBeTrue();
    expect($component->get('redirectUrl'))->toBe('https://example.com/thanks');
    expect($component->get('completionCode'))->toBeNull();

    $submission = Submission::latest()->first();
    expect($submission->completion_code)->toBeNull();
    expect($submission->meta['provider'] ?? null)->toBeNull();
});

it('matches the first provider when multiple detect params are present', function () {
    $form = createFormWithProvider([
        [
            'name' => 'Prolific',
            'detect_param' => 'PROLIFIC_PID',
            'redirect_url' => 'https://prolific.com/complete',
            'passthrough_params' => false,
            'code_type' => 'static',
            'static_code' => 'PRO1',
            'code_param_key' => 'cc',
        ],
        [
            'name' => 'Cint',
            'detect_param' => 'rid',
            'redirect_url' => 'https://cint.com/complete',
            'passthrough_params' => false,
            'code_type' => 'none',
            'static_code' => '',
            'code_param_key' => '',
        ],
    ]);

    $component = Livewire::withQueryParams(['PROLIFIC_PID' => 'p1', 'rid' => 'r1'])
        ->test(FormFill::class, ['slug' => 'provider-test']);

    expect($component->get('matchedProvider')['name'])->toBe('Prolific');
});

it('detects provider even when query capture mode is none', function () {
    $form = createFormWithProvider(
        providers: [
            [
                'name' => 'Prolific',
                'detect_param' => 'PROLIFIC_PID',
                'redirect_url' => 'https://prolific.com/complete',
                'passthrough_params' => false,
                'code_type' => 'static',
                'static_code' => 'CODE1',
                'code_param_key' => 'cc',
            ],
        ],
        extraSettings: [
            'query_capture_mode' => 'none',
        ],
    );

    $component = Livewire::withQueryParams(['PROLIFIC_PID' => 'p1'])
        ->test(FormFill::class, ['slug' => 'provider-test']);

    expect($component->get('matchedProvider')['name'])->toBe('Prolific');

    $component->call('next');
    $component->call('next');

    expect($component->get('redirectUrl'))->toBe('https://prolific.com/complete?cc=CODE1');
});
