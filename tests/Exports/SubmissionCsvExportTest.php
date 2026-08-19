<?php

use BlackpigCreatif\Confessionnal\Enums\FieldType;
use BlackpigCreatif\Confessionnal\Enums\FormMode;
use BlackpigCreatif\Confessionnal\Exports\SubmissionCsvExport;
use BlackpigCreatif\Confessionnal\Models\Form;
use BlackpigCreatif\Confessionnal\Models\FormField;
use BlackpigCreatif\Confessionnal\Models\FormPage;
use BlackpigCreatif\Confessionnal\Models\Section;
use BlackpigCreatif\Confessionnal\Models\Submission;
use Symfony\Component\HttpFoundation\StreamedResponse;

function createExportableForm(): Form
{
    $form = Form::create([
        'name' => 'Export Test',
        'slug' => 'export-test',
        'mode' => FormMode::STANDARD,
        'is_published' => true,
    ]);

    $section = Section::create([
        'form_id' => $form->id,
        'title' => 'Section One',
        'sort_order' => 0,
    ]);

    $page = FormPage::create([
        'section_id' => $section->id,
        'sort_order' => 0,
    ]);

    FormField::create([
        'form_page_id' => $page->id,
        'type' => FieldType::TEXT,
        'label' => 'Full Name',
        'key' => 'full_name',
        'is_required' => true,
        'sort_order' => 0,
    ]);

    FormField::create([
        'form_page_id' => $page->id,
        'type' => FieldType::CHECKBOX,
        'label' => 'Colours',
        'key' => 'colours',
        'is_required' => false,
        'options' => [
            ['label' => 'Red', 'value' => 'red'],
            ['label' => 'Blue', 'value' => 'blue'],
        ],
        'sort_order' => 1,
    ]);

    return $form;
}

function parseCsvResponse(StreamedResponse $response): array
{
    ob_start();
    $response->sendContent();
    $output = ob_get_clean();

    $rows = [];

    foreach (str_getcsv($output, "\n") as $line) {
        $rows[] = str_getcsv($line);
    }

    return $rows;
}

it('generates a streamed CSV response', function () {
    $form = createExportableForm();

    $response = SubmissionCsvExport::download($form);

    expect($response)->toBeInstanceOf(StreamedResponse::class)
        ->and($response->headers->get('Content-Type'))->toBe('text/csv');
});

it('includes field labels as column headers', function () {
    $form = createExportableForm();

    $response = SubmissionCsvExport::download($form);
    $rows = parseCsvResponse($response);

    expect($rows[0])->toBe(['#', 'Full Name', 'Colours', 'Locale', 'Completed At', 'Created At']);
});

it('exports submission data as rows', function () {
    $form = createExportableForm();

    Submission::create([
        'form_id' => $form->id,
        'answers' => ['full_name' => 'Alice', 'colours' => ['red', 'blue']],
        'locale' => 'en',
        'completed_at' => now(),
    ]);

    Submission::create([
        'form_id' => $form->id,
        'answers' => ['full_name' => 'Bob', 'colours' => ['red']],
        'locale' => 'fr',
        'completed_at' => now(),
    ]);

    $response = SubmissionCsvExport::download($form);
    $rows = parseCsvResponse($response);

    // Header + 2 data rows
    expect($rows)->toHaveCount(3)
        ->and($rows[1][1])->toBe('Alice')
        ->and($rows[1][2])->toBe('red, blue')
        ->and($rows[1][3])->toBe('en')
        ->and($rows[2][1])->toBe('Bob')
        ->and($rows[2][2])->toBe('red')
        ->and($rows[2][3])->toBe('fr');
});

it('handles missing answers gracefully', function () {
    $form = createExportableForm();

    Submission::create([
        'form_id' => $form->id,
        'answers' => ['full_name' => 'Charlie'],
        'locale' => 'en',
        'completed_at' => now(),
    ]);

    $response = SubmissionCsvExport::download($form);
    $rows = parseCsvResponse($response);

    // Missing 'colours' should be empty string
    expect($rows[1][1])->toBe('Charlie')
        ->and($rows[1][2])->toBe('');
});

it('exports empty CSV with only headers when no submissions', function () {
    $form = createExportableForm();

    $response = SubmissionCsvExport::download($form);
    $rows = parseCsvResponse($response);

    expect($rows)->toHaveCount(1)
        ->and($rows[0][0])->toBe('#');
});

it('prefixes headers with section title when labels are duplicated across sections', function () {
    $form = Form::create([
        'name' => 'Multi Section Export',
        'slug' => 'multi-section-export',
        'mode' => FormMode::STANDARD,
        'is_published' => true,
    ]);

    foreach (['Brand A', 'Brand B'] as $i => $title) {
        $section = Section::create([
            'form_id' => $form->id,
            'title' => $title,
            'sort_order' => $i,
        ]);

        $page = FormPage::create([
            'section_id' => $section->id,
            'sort_order' => 0,
        ]);

        FormField::create([
            'form_page_id' => $page->id,
            'type' => FieldType::TEXT,
            'label' => 'Rating',
            'key' => $i === 0 ? 'rating' : 'rating_2',
            'sort_order' => 0,
        ]);
    }

    $response = SubmissionCsvExport::download($form);
    $rows = parseCsvResponse($response);

    expect($rows[0])->toBe(['#', 'Brand A - Rating', 'Brand B - Rating', 'Locale', 'Completed At', 'Created At']);
});

it('does not prefix headers when labels are unique across sections', function () {
    $form = Form::create([
        'name' => 'Unique Labels Export',
        'slug' => 'unique-labels-export',
        'mode' => FormMode::STANDARD,
        'is_published' => true,
    ]);

    foreach (['Part 1', 'Part 2'] as $i => $title) {
        $section = Section::create([
            'form_id' => $form->id,
            'title' => $title,
            'sort_order' => $i,
        ]);

        $page = FormPage::create([
            'section_id' => $section->id,
            'sort_order' => 0,
        ]);

        FormField::create([
            'form_page_id' => $page->id,
            'type' => FieldType::TEXT,
            'label' => $i === 0 ? 'Name' : 'Email',
            'key' => $i === 0 ? 'name' : 'email',
            'sort_order' => 0,
        ]);
    }

    $response = SubmissionCsvExport::download($form);
    $rows = parseCsvResponse($response);

    expect($rows[0])->toBe(['#', 'Name', 'Email', 'Locale', 'Completed At', 'Created At']);
});

it('names the file using the form slug and date', function () {
    $form = createExportableForm();

    $response = SubmissionCsvExport::download($form);
    $disposition = $response->headers->get('Content-Disposition');

    expect($disposition)->toContain('export-test-submissions-')
        ->and($disposition)->toContain(now()->format('Y-m-d'));
});
