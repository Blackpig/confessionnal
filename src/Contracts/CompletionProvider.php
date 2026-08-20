<?php

namespace BlackpigCreatif\Confessionnal\Contracts;

use Filament\Forms\Components\Component;

interface CompletionProvider
{
    public static function getName(): string;

    public static function getDescription(): ?string;

    /** @return array<string> Query param names that identify this provider */
    public static function getDetectParams(): array;

    /** @return array<string, mixed> Default config values applied when provider is selected */
    public static function getDefaults(): array;

    /** @return array<Component> Filament form components for provider config */
    public static function getConfigSchema(): array;

    /** Check if the provider is detected in the given query params */
    public function detect(array $queryParams, array $config): bool;

    /** Generate a completion code (or null if this provider doesn't use codes) */
    public function generateCode(array $config): ?string;

    /** Build the redirect URL for this provider */
    public function buildRedirectUrl(array $config, ?string $code, array $capturedParams): ?string;
}
