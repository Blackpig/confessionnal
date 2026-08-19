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

function createFormWithFields(FormMode $mode = FormMode::CONVERSATIONAL, int $fieldCount = 2): Form
{
    $form = Form::create([
        'name' => 'Test Form',
        'slug' => 'test-form',
        'mode' => $mode,
        'is_published' => true,
    ]);

    $section = Section::create([
        'form_id' => $form->id,
        'title' => 'Test Section',
        'subtext' => 'Some intro text',
        'sort_order' => 0,
    ]);

    $page = FormPage::create([
        'section_id' => $section->id,
        'sort_order' => 0,
    ]);

    for ($i = 0; $i < $fieldCount; $i++) {
        FormField::create([
            'form_page_id' => $page->id,
            'type' => FieldType::TEXT,
            'label' => "Question " . ($i + 1),
            'key' => "question_" . ($i + 1),
            'is_required' => $i === 0,
            'sort_order' => $i,
        ]);
    }

    return $form;
}

it('renders the fill route for a published form', function () {
    $form = createFormWithFields();

    $this->get(route('confessionnal.fill', ['slug' => 'test-form']))
        ->assertOk();
});

it('returns 404 for unpublished forms', function () {
    $form = createFormWithFields();
    $form->update(['is_published' => false]);

    $this->get(route('confessionnal.fill', ['slug' => 'test-form']))
        ->assertNotFound();
});

it('returns 404 for non-existent slugs', function () {
    $this->get(route('confessionnal.fill', ['slug' => 'nope']))
        ->assertNotFound();
});

it('builds steps with section intro for conversational mode', function () {
    $form = createFormWithFields(FormMode::CONVERSATIONAL, 2);

    $component = Livewire::test(FormFill::class, ['slug' => 'test-form']);

    // Section intro + 2 individual field steps = 3 steps
    $component->assertSet('steps', function (array $steps) {
        return count($steps) === 3
            && $steps[0]['type'] === 'intro'
            && $steps[1]['type'] === 'question'
            && $steps[2]['type'] === 'question'
            && count($steps[1]['fields']) === 1
            && count($steps[2]['fields']) === 1;
    });
});

it('groups fields per page in standard mode', function () {
    $form = createFormWithFields(FormMode::STANDARD, 3);

    $component = Livewire::test(FormFill::class, ['slug' => 'test-form']);

    // Section intro + 1 page step (all 3 fields) = 2 steps
    $component->assertSet('steps', function (array $steps) {
        return count($steps) === 2
            && $steps[0]['type'] === 'intro'
            && $steps[1]['type'] === 'question'
            && count($steps[1]['fields']) === 3;
    });
});

it('navigates forward through steps', function () {
    $form = createFormWithFields(FormMode::CONVERSATIONAL, 1);

    Livewire::test(FormFill::class, ['slug' => 'test-form'])
        ->assertSet('currentStep', 0)
        ->call('next') // Skip intro
        ->assertSet('currentStep', 1);
});

it('navigates backward through steps', function () {
    $form = createFormWithFields(FormMode::CONVERSATIONAL, 1);

    Livewire::test(FormFill::class, ['slug' => 'test-form'])
        ->call('next') // Skip intro
        ->assertSet('currentStep', 1)
        ->call('previous')
        ->assertSet('currentStep', 0);
});

it('validates required fields before advancing', function () {
    $form = createFormWithFields(FormMode::CONVERSATIONAL, 1);

    Livewire::test(FormFill::class, ['slug' => 'test-form'])
        ->call('next') // Skip intro
        ->assertSet('currentStep', 1)
        ->call('next') // Try to advance without filling required field
        ->assertSet('currentStep', 1) // Should stay on same step
        ->assertSet('stepErrors', function (array $errors) {
            return ! empty($errors);
        });
});

it('advances after filling required fields', function () {
    $form = createFormWithFields(FormMode::CONVERSATIONAL, 1);

    Livewire::test(FormFill::class, ['slug' => 'test-form'])
        ->call('next') // Skip intro
        ->set('answers.question_1', 'My answer')
        ->call('next') // Should submit since this is the last step
        ->assertSet('completed', true);
});

it('creates a submission on completion', function () {
    $form = createFormWithFields(FormMode::CONVERSATIONAL, 1);

    expect(Submission::count())->toBe(0);

    Livewire::test(FormFill::class, ['slug' => 'test-form'])
        ->call('next') // Skip intro
        ->set('answers.question_1', 'My answer')
        ->call('next'); // Submit

    expect(Submission::count())->toBe(1);

    $submission = Submission::first();
    expect($submission->form_id)->toBe($form->id)
        ->and($submission->answers)->toBe(['question_1' => 'My answer'])
        ->and($submission->locale)->toBe('en')
        ->and($submission->completed_at)->not->toBeNull();
});

it('records section order in submission', function () {
    $form = createFormWithFields(FormMode::CONVERSATIONAL, 1);

    Livewire::test(FormFill::class, ['slug' => 'test-form'])
        ->call('next')
        ->set('answers.question_1', 'answer')
        ->call('next');

    $submission = Submission::first();
    expect($submission->section_order)->toBeArray()
        ->and($submission->section_order)->toHaveCount(1);
});

it('sets locale from route parameter', function () {
    $form = createFormWithFields(FormMode::CONVERSATIONAL, 1);

    Livewire::test(FormFill::class, ['slug' => 'test-form', 'locale' => 'fr'])
        ->assertSet('locale', 'fr');
});

it('calculates progress correctly', function () {
    $form = createFormWithFields(FormMode::CONVERSATIONAL, 2);

    // 3 steps total: intro(0), q1(1), q2(2)
    Livewire::test(FormFill::class, ['slug' => 'test-form'])
        ->assertSee('width: 0%')        // Step 0 of 2
        ->call('next')                    // Skip intro -> step 1
        ->assertSee('width: 50%')        // Step 1 of 2
        ->set('answers.question_1', 'a')
        ->call('next')                    // -> step 2
        ->assertSee('width: 100%');       // Step 2 of 2
});

it('validates checkbox fields as arrays', function () {
    $form = Form::create([
        'name' => 'Checkbox Form',
        'slug' => 'checkbox-form',
        'mode' => FormMode::STANDARD,
        'is_published' => true,
    ]);

    $section = Section::create([
        'form_id' => $form->id,
        'sort_order' => 0,
    ]);

    $page = FormPage::create([
        'section_id' => $section->id,
        'sort_order' => 0,
    ]);

    FormField::create([
        'form_page_id' => $page->id,
        'type' => FieldType::CHECKBOX,
        'label' => 'Colours',
        'key' => 'colours',
        'is_required' => true,
        'options' => [
            ['label' => 'Red', 'value' => 'red'],
            ['label' => 'Blue', 'value' => 'blue'],
        ],
        'sort_order' => 0,
    ]);

    Livewire::test(FormFill::class, ['slug' => 'checkbox-form'])
        ->assertSet('answers.colours', []) // Initialised as array
        ->call('next') // Try to submit without answering
        ->assertSet('stepErrors', function (array $errors) {
            return ! empty($errors);
        });
});

// --- Conditional Logic ---

it('hides fields when condition is not met', function () {
    $form = Form::create([
        'name' => 'Conditional Form',
        'slug' => 'conditional-form',
        'mode' => FormMode::STANDARD,
        'is_published' => true,
    ]);

    $section = Section::create([
        'form_id' => $form->id,
        'sort_order' => 0,
    ]);

    $page = FormPage::create([
        'section_id' => $section->id,
        'sort_order' => 0,
    ]);

    FormField::create([
        'form_page_id' => $page->id,
        'type' => FieldType::SELECT,
        'label' => 'Country',
        'key' => 'country',
        'is_required' => true,
        'options' => [
            ['label' => 'France', 'value' => 'France'],
            ['label' => 'UK', 'value' => 'UK'],
        ],
        'sort_order' => 0,
    ]);

    FormField::create([
        'form_page_id' => $page->id,
        'type' => FieldType::TEXT,
        'label' => 'SIRET Number',
        'key' => 'siret',
        'is_required' => true,
        'conditional_logic' => [
            'field_key' => 'country',
            'operator' => 'equals',
            'value' => 'France',
        ],
        'sort_order' => 1,
    ]);

    // Without selecting France, SIRET should not appear
    Livewire::test(FormFill::class, ['slug' => 'conditional-form'])
        ->assertDontSee('SIRET Number');
});

it('shows fields when condition is met', function () {
    $form = Form::create([
        'name' => 'Conditional Show',
        'slug' => 'conditional-show',
        'mode' => FormMode::STANDARD,
        'is_published' => true,
    ]);

    $section = Section::create([
        'form_id' => $form->id,
        'sort_order' => 0,
    ]);

    $page = FormPage::create([
        'section_id' => $section->id,
        'sort_order' => 0,
    ]);

    FormField::create([
        'form_page_id' => $page->id,
        'type' => FieldType::SELECT,
        'label' => 'Country',
        'key' => 'country',
        'is_required' => true,
        'options' => [
            ['label' => 'France', 'value' => 'France'],
            ['label' => 'UK', 'value' => 'UK'],
        ],
        'sort_order' => 0,
    ]);

    FormField::create([
        'form_page_id' => $page->id,
        'type' => FieldType::TEXT,
        'label' => 'SIRET Number',
        'key' => 'siret',
        'is_required' => true,
        'conditional_logic' => [
            'field_key' => 'country',
            'operator' => 'equals',
            'value' => 'France',
        ],
        'sort_order' => 1,
    ]);

    // After selecting France, SIRET should appear
    Livewire::test(FormFill::class, ['slug' => 'conditional-show'])
        ->set('answers.country', 'France')
        ->assertSee('SIRET Number');
});

it('skips validation for hidden conditional fields', function () {
    $form = Form::create([
        'name' => 'Conditional Validation',
        'slug' => 'conditional-validation',
        'mode' => FormMode::STANDARD,
        'is_published' => true,
    ]);

    $section = Section::create([
        'form_id' => $form->id,
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
        'is_required' => true,
        'sort_order' => 0,
    ]);

    FormField::create([
        'form_page_id' => $page->id,
        'type' => FieldType::TEXT,
        'label' => 'Company',
        'key' => 'company',
        'is_required' => true,
        'conditional_logic' => [
            'field_key' => 'name',
            'operator' => 'is_filled',
            'value' => null,
        ],
        'sort_order' => 1,
    ]);

    // Name is empty, so 'company' condition is not met. Only 'name' should validate.
    Livewire::test(FormFill::class, ['slug' => 'conditional-validation'])
        ->set('answers.name', 'Alice')
        ->call('next') // company is now visible but empty, should fail
        ->assertSet('stepErrors', fn (array $errors) => ! empty($errors));
});

it('skips hidden steps in conversational mode', function () {
    $form = Form::create([
        'name' => 'Skip Steps',
        'slug' => 'skip-steps',
        'mode' => FormMode::CONVERSATIONAL,
        'is_published' => true,
    ]);

    $section = Section::create([
        'form_id' => $form->id,
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
        'is_required' => true,
        'sort_order' => 0,
    ]);

    FormField::create([
        'form_page_id' => $page->id,
        'type' => FieldType::TEXT,
        'label' => 'Hidden Field',
        'key' => 'hidden_field',
        'is_required' => false,
        'conditional_logic' => [
            'field_key' => 'name',
            'operator' => 'equals',
            'value' => 'SHOWME',
        ],
        'sort_order' => 1,
    ]);

    FormField::create([
        'form_page_id' => $page->id,
        'type' => FieldType::TEXT,
        'label' => 'Final Field',
        'key' => 'final_field',
        'is_required' => false,
        'sort_order' => 2,
    ]);

    // No section title, so no intro step.
    // Steps: name(0), hidden_field(1), final_field(2)
    // Typing "Alice" (not "SHOWME") should skip step 1
    Livewire::test(FormFill::class, ['slug' => 'skip-steps'])
        ->assertSet('currentStep', 0)
        ->set('answers.name', 'Alice')
        ->call('next') // should skip step 1 (hidden), land on step 2 (final_field)
        ->assertSet('currentStep', 2)
        ->assertSee('Final Field');
});
