<?php

namespace BlackpigCreatif\Confessionnal\CompletionProviders;

class ProlificProvider extends BaseCompletionProvider
{
    public static function getName(): string
    {
        return 'Prolific';
    }

    public static function getDescription(): ?string
    {
        return 'Detects respondents via the PROLIFIC_PID query parameter.';
    }

    public static function getDetectParams(): array
    {
        return ['PROLIFIC_PID'];
    }

    public static function getDefaults(): array
    {
        return [
            'redirect_url' => 'https://app.prolific.com/submissions/complete',
            'passthrough_params' => false,
            'code_type' => 'static',
            'static_code' => '',
            'code_param_key' => 'cc',
        ];
    }

    public static function getConfigSchema(): array
    {
        return [
            static::redirectUrlField('https://app.prolific.com/submissions/complete'),
            ...static::codeFields(defaultType: 'static', defaultParamKey: 'cc'),
        ];
    }
}
