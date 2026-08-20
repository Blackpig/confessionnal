<?php

namespace BlackpigCreatif\Confessionnal\CompletionProviders;

class CintProvider extends BaseCompletionProvider
{
    public static function getName(): string
    {
        return 'Cint';
    }

    public static function getDescription(): ?string
    {
        return 'Detects respondents via the rid query parameter.';
    }

    public static function getDetectParams(): array
    {
        return ['rid'];
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
                ->helperText('Cint redirect URL with your ProjectToken (e.g. https://s.cint.com/Survey/Complete?ProjectToken=XYZ).'),
            static::passthroughField(default: true),
            ...static::codeFields(),
        ];
    }
}
