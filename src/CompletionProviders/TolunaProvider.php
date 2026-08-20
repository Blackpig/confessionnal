<?php

namespace BlackpigCreatif\Confessionnal\CompletionProviders;

class TolunaProvider extends BaseCompletionProvider
{
    public static function getName(): string
    {
        return 'Toluna';
    }

    public static function getDescription(): ?string
    {
        return 'Detects respondents via the respondentid query parameter.';
    }

    public static function getDetectParams(): array
    {
        return ['respondentid'];
    }

    public static function getDefaults(): array
    {
        return [
            'redirect_url' => '',
            'passthrough_params' => true,
            'code_type' => 'none',
            'static_code' => '',
            'code_param_key' => '',
        ];
    }

    public static function getConfigSchema(): array
    {
        return [
            static::redirectUrlField()
                ->required()
                ->helperText('Toluna redirect URL for your survey.'),
            static::passthroughField(default: true),
            ...static::codeFields(),
        ];
    }
}
