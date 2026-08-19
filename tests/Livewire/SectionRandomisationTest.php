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

function createRandomisationForm(array $sections): Form
{
    $form = Form::create([
        'name' => 'Randomisation Test',
        'slug' => 'randomisation-test',
        'mode' => FormMode::CONVERSATIONAL,
        'is_published' => true,
    ]);

    foreach ($sections as $i => $sectionData) {
        $section = Section::create([
            'form_id' => $form->id,
            'title' => $sectionData['title'],
            'sort_order' => $i,
            'randomisation_group' => $sectionData['group'] ?? null,
        ]);

        $page = FormPage::create([
            'section_id' => $section->id,
            'sort_order' => 0,
        ]);

        FormField::create([
            'form_page_id' => $page->id,
            'type' => FieldType::TEXT,
            'label' => $sectionData['title'] . ' Q1',
            'key' => 'q_' . $i,
            'is_required' => false,
            'sort_order' => 0,
        ]);
    }

    return $form;
}

it('records section order on submission', function () {
    $form = createRandomisationForm([
        ['title' => 'Intro', 'group' => null],
        ['title' => 'Brand A', 'group' => 'brands'],
        ['title' => 'Brand B', 'group' => 'brands'],
    ]);

    $sectionIds = $form->sections()->orderBy('sort_order')->pluck('id')->toArray();

    $component = Livewire::test(FormFill::class, ['slug' => 'randomisation-test']);

    // The sectionOrder should contain all 3 section IDs
    $component->assertSet('sectionOrder', function (array $order) use ($sectionIds) {
        return count($order) === 3
            && empty(array_diff($order, $sectionIds));
    });

    // Walk through all steps and submit
    $steps = $component->get('steps');

    for ($i = 0; $i < count($steps) - 1; $i++) {
        $component->call('next');
    }

    $component->call('next'); // submit on last step

    $submission = Submission::first();
    expect($submission->section_order)->toBeArray()
        ->and($submission->section_order)->toHaveCount(3);
});

it('does not shuffle sections without a randomisation group', function () {
    $form = createRandomisationForm([
        ['title' => 'Part 1', 'group' => null],
        ['title' => 'Part 2', 'group' => null],
        ['title' => 'Part 3', 'group' => null],
    ]);

    $expectedOrder = $form->sections()->orderBy('sort_order')->pluck('id')->toArray();

    // Run multiple times to confirm order is always preserved
    for ($run = 0; $run < 5; $run++) {
        $component = Livewire::test(FormFill::class, ['slug' => 'randomisation-test']);
        $sectionOrder = $component->get('sectionOrder');
        expect($sectionOrder)->toBe($expectedOrder);
    }
});

it('only shuffles sections within the same randomisation group', function () {
    // Fixed section first, then two grouped sections
    $form = createRandomisationForm([
        ['title' => 'Welcome', 'group' => null],
        ['title' => 'Brand A', 'group' => 'brands'],
        ['title' => 'Brand B', 'group' => 'brands'],
    ]);

    $sections = $form->sections()->orderBy('sort_order')->get();
    $welcomeId = $sections[0]->id;

    // Run many times: Welcome should always be first
    $sawBothOrders = false;
    $orders = [];

    for ($run = 0; $run < 30; $run++) {
        $component = Livewire::test(FormFill::class, ['slug' => 'randomisation-test']);
        $sectionOrder = $component->get('sectionOrder');

        // Welcome (ungrouped) should always be first
        expect($sectionOrder[0])->toBe($welcomeId);

        $orders[] = $sectionOrder;
    }

    // With 30 runs, probability of never seeing a swap is (0.5)^30 ~ 1e-9
    $uniqueOrders = array_unique(array_map('json_encode', $orders));
    expect(count($uniqueOrders))->toBeGreaterThan(1);
});

it('keeps question pages in fixed order within a shuffled section', function () {
    $form = Form::create([
        'name' => 'Page Order Test',
        'slug' => 'page-order-test',
        'mode' => FormMode::CONVERSATIONAL,
        'is_published' => true,
    ]);

    $section = Section::create([
        'form_id' => $form->id,
        'title' => 'Brand A',
        'sort_order' => 0,
        'randomisation_group' => 'brands',
    ]);

    // Create 3 pages with specific ordering
    foreach (range(0, 2) as $i) {
        $page = FormPage::create([
            'section_id' => $section->id,
            'sort_order' => $i,
        ]);

        FormField::create([
            'form_page_id' => $page->id,
            'type' => FieldType::TEXT,
            'label' => 'Q' . ($i + 1),
            'key' => "q_{$i}",
            'is_required' => false,
            'sort_order' => 0,
        ]);
    }

    $component = Livewire::test(FormFill::class, ['slug' => 'page-order-test']);
    $steps = $component->get('steps');

    // Intro + 3 question steps = 4
    expect($steps)->toHaveCount(4);

    // Question steps should maintain page order
    $questionSteps = array_values(array_filter($steps, fn ($s) => $s['type'] === 'question'));
    expect($questionSteps[0]['fields'][0]['key'])->toBe('q_0')
        ->and($questionSteps[1]['fields'][0]['key'])->toBe('q_1')
        ->and($questionSteps[2]['fields'][0]['key'])->toBe('q_2');
});

it('shuffles sections across multiple groups independently', function () {
    $form = createRandomisationForm([
        ['title' => 'Brand A', 'group' => 'brands'],
        ['title' => 'Brand B', 'group' => 'brands'],
        ['title' => 'Colour X', 'group' => 'colours'],
        ['title' => 'Colour Y', 'group' => 'colours'],
    ]);

    $sections = $form->sections()->orderBy('sort_order')->get();

    // Brands should always appear before colours (group order is preserved)
    // but within each group, order can be shuffled
    for ($run = 0; $run < 10; $run++) {
        $component = Livewire::test(FormFill::class, ['slug' => 'randomisation-test']);
        $sectionOrder = $component->get('sectionOrder');

        $brandIds = [$sections[0]->id, $sections[1]->id];
        $colourIds = [$sections[2]->id, $sections[3]->id];

        // First two should be brands (in either order)
        expect(in_array($sectionOrder[0], $brandIds))->toBeTrue()
            ->and(in_array($sectionOrder[1], $brandIds))->toBeTrue()
            // Last two should be colours (in either order)
            ->and(in_array($sectionOrder[2], $colourIds))->toBeTrue()
            ->and(in_array($sectionOrder[3], $colourIds))->toBeTrue();
    }
});
