<?php

namespace BlackpigCreatif\Confessionnal\CompletionProviders;

use Filament\Forms\Components\TextInput;

class GenericProvider extends BaseCompletionProvider
{
    public static function getName(): string
    {
        return 'Generic';
    }

    public static function getDescription(): ?string
    {
        return 'Fully configurable provider. Set your own detection parameter, redirect URL, and completion code.';
    }

    public static function getDetectParams(): array
    {
        return [];
    }

    public static function getDefaults(): array
    {
        return [
            'detect_param' => '',
            'redirect_url' => '',
            'passthrough_params' => false,
            'code_type' => 'none',
            'static_code' => '',
            'code_param_key' => '',
        ];
    }

    public static function getConfigSchema(): array
    {
        return [
            TextInput::make('detect_param')
                ->label('Detection parameter')
                ->helperText('The query parameter that identifies respondents from this provider.')
                ->required(),
            static::redirectUrlField(),
            static::passthroughField(),
            ...static::codeFields(),
        ];
    }

    public function detect(array $queryParams, array $config): bool
    {
        $param = $config['detect_param'] ?? null;

        return $param && array_key_exists($param, $queryParams);
    }
}
