<?php

namespace BlackpigCreatif\Confessionnal\Support;

class ConditionEvaluator
{
    public static function isMet(?array $condition, array $answers): bool
    {
        if (empty($condition)) {
            return true;
        }

        $fieldKey = $condition['field_key'] ?? null;
        $operator = $condition['operator'] ?? null;
        $value = $condition['value'] ?? null;

        if (! $fieldKey || ! $operator) {
            return true;
        }

        $answer = $answers[$fieldKey] ?? null;

        return match ($operator) {
            'equals' => $answer == $value,
            'not_equals' => $answer != $value,
            'contains' => is_array($answer)
                ? in_array($value, $answer)
                : str_contains((string) $answer, (string) $value),
            'not_contains' => is_array($answer)
                ? ! in_array($value, $answer)
                : ! str_contains((string) $answer, (string) $value),
            'is_filled' => ! empty($answer),
            'is_empty' => empty($answer),
            default => true,
        };
    }
}
