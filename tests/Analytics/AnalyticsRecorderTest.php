<?php

use BlackpigCreatif\Confessionnal\Enums\FieldType;
use BlackpigCreatif\Confessionnal\Enums\FormMode;
use BlackpigCreatif\Confessionnal\Livewire\FormFill;
use BlackpigCreatif\Confessionnal\Models\Form;
use BlackpigCreatif\Confessionnal\Models\FormAnalytic;
use BlackpigCreatif\Confessionnal\Models\FormField;
use BlackpigCreatif\Confessionnal\Models\FormPage;
use BlackpigCreatif\Confessionnal\Models\PageAnalytic;
use BlackpigCreatif\Confessionnal\Models\Section;
use Livewire\Livewire;

function createAnalyticsForm(FormMode $mode = FormMode::CONVERSATIONAL): Form
{
    $form = Form::create([
        'name' => 'Analytics Form',
        'slug' => 'analytics-form',
        'mode' => $mode,
        'is_published' => true,
    ]);

    $section = Section::create([
        'form_id' => $form->id,
        'title' => 'Section One',
        'sort_order' => 0,
    ]);

    $page1 = FormPage::create([
        'section_id' => $section->id,
        'sort_order' => 0,
    ]);

    FormField::create([
        'form_page_id' => $page1->id,
        'type' => FieldType::TEXT,
        'label' => 'Name',
        'key' => 'name',
        'is_required' => false,
        'sort_order' => 0,
    ]);

    $page2 = FormPage::create([
        'section_id' => $section->id,
        'sort_order' => 1,
    ]);

    FormField::create([
        'form_page_id' => $page2->id,
        'type' => FieldType::TEXT,
        'label' => 'Email',
        'key' => 'email',
        'is_required' => false,
        'sort_order' => 0,
    ]);

    return $form;
}

it('records a view when the form is loaded', function () {
    $form = createAnalyticsForm();

    Livewire::test(FormFill::class, ['slug' => 'analytics-form']);

    $analytic = FormAnalytic::where('form_id', $form->id)->first();
    expect($analytic)->not->toBeNull();
    expect($analytic->views)->toBe(1);
    expect($analytic->starts)->toBe(0);
    expect($analytic->completions)->toBe(0);
});

it('records a start when the user advances past the first step', function () {
    $form = createAnalyticsForm();

    $component = Livewire::test(FormFill::class, ['slug' => 'analytics-form']);

    // First step is the section intro, advance past it
    $component->call('next');

    $analytic = FormAnalytic::where('form_id', $form->id)->first();
    expect($analytic->starts)->toBe(1);
});

it('records a completion on form submission', function () {
    $form = createAnalyticsForm();

    $component = Livewire::test(FormFill::class, ['slug' => 'analytics-form']);

    // Advance through: intro -> page 1 -> page 2 -> submit
    $component->call('next'); // past intro
    $component->call('next'); // past page 1
    $component->call('next'); // past page 2 (submit)

    $analytic = FormAnalytic::where('form_id', $form->id)->first();
    expect($analytic->completions)->toBe(1);
});

it('records page views for question steps', function () {
    $form = createAnalyticsForm();

    $component = Livewire::test(FormFill::class, ['slug' => 'analytics-form']);

    // Mount shows first step (intro), no page view for intro
    // Advance to page 1
    $component->call('next');

    $pageViews = PageAnalytic::where('form_id', $form->id)->get();
    expect($pageViews)->toHaveCount(1);
    expect($pageViews->first()->views)->toBe(1);

    // Advance to page 2
    $component->call('next');

    $pageViews = PageAnalytic::where('form_id', $form->id)->get();
    expect($pageViews)->toHaveCount(2);
});

it('increments counts on the same day rather than creating new rows', function () {
    $form = createAnalyticsForm();

    // Two separate form loads
    Livewire::test(FormFill::class, ['slug' => 'analytics-form']);
    Livewire::test(FormFill::class, ['slug' => 'analytics-form']);

    $analytics = FormAnalytic::where('form_id', $form->id)->get();
    expect($analytics)->toHaveCount(1);
    expect($analytics->first()->views)->toBe(2);
});

it('does not record analytics in preview mode', function () {
    $form = createAnalyticsForm();
    $form->update(['is_published' => false]);

    $url = \Illuminate\Support\Facades\URL::signedRoute('confessionnal.fill', [
        'slug' => 'analytics-form',
        'preview' => 1,
    ]);

    $this->get($url)->assertOk();

    expect(FormAnalytic::where('form_id', $form->id)->count())->toBe(0);
    expect(PageAnalytic::where('form_id', $form->id)->count())->toBe(0);
});

it('records start only once per session', function () {
    $form = createAnalyticsForm();

    $component = Livewire::test(FormFill::class, ['slug' => 'analytics-form']);

    // Advance past intro (records start)
    $component->call('next');
    // Go back
    $component->call('previous');
    // Advance again (should NOT record another start)
    $component->call('next');

    $analytic = FormAnalytic::where('form_id', $form->id)->first();
    expect($analytic->starts)->toBe(1);
});

it('provides an analytics summary on the form model', function () {
    $form = createAnalyticsForm();

    FormAnalytic::create([
        'form_id' => $form->id,
        'date' => now()->subDay(),
        'views' => 100,
        'starts' => 80,
        'completions' => 40,
    ]);

    FormAnalytic::create([
        'form_id' => $form->id,
        'date' => now(),
        'views' => 50,
        'starts' => 30,
        'completions' => 10,
    ]);

    $summary = $form->analyticsSummary();

    expect($summary['views'])->toBe(150);
    expect($summary['starts'])->toBe(110);
    expect($summary['completions'])->toBe(50);
    expect($summary['start_rate'])->toBe(73.3);
    expect($summary['completion_rate'])->toBe(45.5);
});

it('filters analytics summary by date range', function () {
    $form = createAnalyticsForm();

    FormAnalytic::create([
        'form_id' => $form->id,
        'date' => now()->subDays(10),
        'views' => 100,
        'starts' => 80,
        'completions' => 40,
    ]);

    FormAnalytic::create([
        'form_id' => $form->id,
        'date' => now(),
        'views' => 50,
        'starts' => 30,
        'completions' => 10,
    ]);

    $summary = $form->analyticsSummary(from: now()->subDay());

    expect($summary['views'])->toBe(50);
    expect($summary['starts'])->toBe(30);
    expect($summary['completions'])->toBe(10);
});
