<?php

use BlackpigCreatif\Confessionnal\Enums\FieldType;
use BlackpigCreatif\Confessionnal\Enums\FormMode;
use BlackpigCreatif\Confessionnal\Livewire\FormFill;
use BlackpigCreatif\Confessionnal\Models\Form;
use BlackpigCreatif\Confessionnal\Models\FormField;
use BlackpigCreatif\Confessionnal\Models\FormPage;
use BlackpigCreatif\Confessionnal\Models\Section;
use BlackpigCreatif\Confessionnal\Models\Submission;
use Illuminate\Support\Facades\URL;

function createPreviewForm(bool $published = true): Form
{
    $form = Form::create([
        'name' => 'Preview Test',
        'slug' => 'preview-test',
        'mode' => FormMode::CONVERSATIONAL,
        'is_published' => $published,
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

it('renders an unpublished form via signed preview URL', function () {
    createPreviewForm(published: false);

    $url = URL::signedRoute('confessionnal.fill', [
        'slug' => 'preview-test',
        'preview' => 1,
    ]);

    $this->get($url)->assertOk();
});

it('rejects preview without a valid signature', function () {
    createPreviewForm(published: false);

    $this->get(route('confessionnal.fill', [
        'slug' => 'preview-test',
        'preview' => 1,
    ]))->assertNotFound();
});

it('sets preview mode on the component', function () {
    createPreviewForm();

    $url = URL::signedRoute('confessionnal.fill', [
        'slug' => 'preview-test',
        'preview' => 1,
    ]);

    $this->get($url)
        ->assertOk()
        ->assertSee('Preview mode');
});

it('does not create a submission in preview mode', function () {
    createPreviewForm();

    $url = URL::signedRoute('confessionnal.fill', [
        'slug' => 'preview-test',
        'preview' => 1,
    ]);

    // Load via signed URL to set preview mode, then use Livewire to interact
    $this->get($url);

    $component = \Livewire\Livewire::test(FormFill::class, ['slug' => 'preview-test'])
        ->set('preview', true);

    // Walk through: intro -> question -> submit
    $component->call('next'); // past intro
    $component->set('answers.name', 'Test');
    $component->call('next'); // submit

    expect($component->get('completed'))->toBeTrue()
        ->and(Submission::count())->toBe(0);
});

it('does not show preview banner for normal visitors', function () {
    createPreviewForm();

    $this->get(route('confessionnal.fill', ['slug' => 'preview-test']))
        ->assertOk()
        ->assertDontSee('Preview mode');
});

it('creates a submission for normal (non-preview) form fills', function () {
    createPreviewForm();

    $component = \Livewire\Livewire::test(FormFill::class, ['slug' => 'preview-test']);

    $component->call('next'); // past intro
    $component->set('answers.name', 'Alice');
    $component->call('next'); // submit

    expect($component->get('completed'))->toBeTrue()
        ->and(Submission::count())->toBe(1);
});
