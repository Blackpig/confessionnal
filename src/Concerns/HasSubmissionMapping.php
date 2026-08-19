<?php

namespace BlackpigCreatif\Confessionnal\Concerns;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait HasSubmissionMapping
{
    public static function getMappingName(): string
    {
        return class_basename(static::class);
    }

    /** @return array<string, string> */
    public static function getMappableColumns(): array
    {
        $model = new static;

        // Use explicit $mappable property if defined
        if (property_exists($model, 'mappable') && ! empty($model->mappable)) {
            return collect($model->mappable)
                ->mapWithKeys(fn (string $col) => [$col => Str::headline($col)])
                ->toArray();
        }

        // Fall back to schema auto-derivation
        $excluded = [
            'id',
            'created_at',
            'updated_at',
            'deleted_at',
            'remember_token',
            'email_verified_at',
            'password',
        ];

        return collect(Schema::getColumnListing($model->getTable()))
            ->reject(fn (string $col) => in_array($col, $excluded))
            ->mapWithKeys(fn (string $col) => [$col => Str::headline($col)])
            ->toArray();
    }
}
