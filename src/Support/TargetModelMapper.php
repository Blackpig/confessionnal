<?php

namespace BlackpigCreatif\Confessionnal\Support;

use BlackpigCreatif\Confessionnal\Contracts\CanReceiveSubmissions;
use BlackpigCreatif\Confessionnal\Models\Form;
use BlackpigCreatif\Confessionnal\Models\Submission;
use Illuminate\Database\Eloquent\Model;

class TargetModelMapper
{
    public static function handle(Form $form, Submission $submission): ?Model
    {
        $settings = $form->settings ?? [];
        $targetClass = $settings['target_model'] ?? null;
        $fieldMapping = $settings['field_mapping'] ?? [];

        if (! $targetClass || empty($fieldMapping)) {
            return null;
        }

        if (! class_exists($targetClass)) {
            return null;
        }

        $attributes = static::mapAttributes($submission->answers, $fieldMapping);

        if (empty($attributes)) {
            return null;
        }

        $mode = $settings['target_mode'] ?? 'create';
        $findBy = $settings['target_find_by'] ?? null;

        if ($mode === 'update' && $findBy && isset($attributes[$findBy])) {
            return $targetClass::updateOrCreate(
                [$findBy => $attributes[$findBy]],
                $attributes,
            );
        }

        return $targetClass::create($attributes);
    }

    protected static function mapAttributes(array $answers, array $fieldMapping): array
    {
        $attributes = [];

        foreach ($fieldMapping as $mapping) {
            $fieldKey = $mapping['field_key'] ?? null;
            $column = $mapping['model_column'] ?? null;

            if (! $fieldKey || ! $column) {
                continue;
            }

            if (array_key_exists($fieldKey, $answers)) {
                $value = $answers[$fieldKey];

                if (is_array($value)) {
                    $value = implode(', ', $value);
                }

                $attributes[$column] = $value;
            }
        }

        return $attributes;
    }
}
