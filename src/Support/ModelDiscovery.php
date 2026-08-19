<?php

namespace BlackpigCreatif\Confessionnal\Support;

use BlackpigCreatif\Confessionnal\Contracts\CanReceiveSubmissions;
use Illuminate\Support\Facades\File;

class ModelDiscovery
{
    /** @return array<class-string, string> FQCN => display name */
    public static function discover(): array
    {
        $models = [];

        // Scan app/Models directory
        $modelsPath = app_path('Models');

        if (is_dir($modelsPath)) {
            foreach (File::allFiles($modelsPath) as $file) {
                $class = 'App\\Models\\' . str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    $file->getRelativePathname(),
                );

                if (static::isEligible($class)) {
                    $models[$class] = $class::getMappingName();
                }
            }
        }

        // Merge config-registered models
        foreach (config('confessionnal.mappable_models', []) as $class) {
            if (static::isEligible($class)) {
                $models[$class] = $class::getMappingName();
            }
        }

        asort($models);

        return $models;
    }

    protected static function isEligible(string $class): bool
    {
        return class_exists($class)
            && in_array(CanReceiveSubmissions::class, class_implements($class));
    }
}
