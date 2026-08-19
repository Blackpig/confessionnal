<?php

namespace BlackpigCreatif\Confessionnal\Exports;

use BlackpigCreatif\Confessionnal\Models\Form;
use BlackpigCreatif\Confessionnal\Models\FormField;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionCsvExport
{
    public static function download(Form $form): StreamedResponse
    {
        $fields = static::getFields($form);
        $submissions = $form->submissions()->orderBy('created_at')->get();

        $filename = "{$form->slug}-submissions-" . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($fields, $submissions) {
            $handle = fopen('php://output', 'w');

            // Header row
            $headers = ['#', ...static::fieldHeaders($fields), 'Locale', 'Completed At', 'Created At'];
            fputcsv($handle, $headers);

            // Data rows
            foreach ($submissions as $submission) {
                $row = [$submission->id];

                foreach ($fields as $field) {
                    $value = $submission->answers[$field->key] ?? '';

                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }

                    $row[] = $value;
                }

                $row[] = $submission->locale;
                $row[] = $submission->completed_at?->toDateTimeString();
                $row[] = $submission->created_at->toDateTimeString();

                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    protected static function getFields(Form $form): Collection
    {
        return $form->sections()
            ->with(['pages.fields'])
            ->get()
            ->flatMap(fn ($section) => $section->pages)
            ->flatMap(fn ($page) => $page->fields);
    }

    protected static function fieldHeaders(Collection $fields): array
    {
        return $fields->map(fn (FormField $field) => $field->getTranslation('label', app()->getLocale()))
            ->toArray();
    }
}
