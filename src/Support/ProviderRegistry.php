<?php

namespace BlackpigCreatif\Confessionnal\Support;

use BlackpigCreatif\Confessionnal\CompletionProviders\CintProvider;
use BlackpigCreatif\Confessionnal\CompletionProviders\GenericProvider;
use BlackpigCreatif\Confessionnal\CompletionProviders\MTurkProvider;
use BlackpigCreatif\Confessionnal\CompletionProviders\ProlificProvider;
use BlackpigCreatif\Confessionnal\CompletionProviders\TolunaProvider;
use BlackpigCreatif\Confessionnal\Contracts\CompletionProvider;

class ProviderRegistry
{
    /** @var array<class-string<CompletionProvider>> */
    protected static array $builtIn = [
        ProlificProvider::class,
        CintProvider::class,
        TolunaProvider::class,
        MTurkProvider::class,
        GenericProvider::class,
    ];

    /** @return array<class-string<CompletionProvider>> */
    public static function all(): array
    {
        $providers = static::$builtIn;

        foreach (config('confessionnal.completion_providers', []) as $class) {
            if (class_exists($class) && in_array(CompletionProvider::class, class_implements($class))) {
                $providers[] = $class;
            }
        }

        return array_unique($providers);
    }

    /** @return array<class-string<CompletionProvider>, string> */
    public static function options(): array
    {
        $options = [];

        foreach (static::all() as $class) {
            $options[$class] = $class::getName();
        }

        return $options;
    }

    public static function resolve(string $class): ?CompletionProvider
    {
        if (! class_exists($class) || ! in_array(CompletionProvider::class, class_implements($class))) {
            return null;
        }

        return new $class;
    }
}
