<?php

namespace BlackpigCreatif\Confessionnal\CompletionProviders;

class MTurkProvider extends BaseCompletionProvider
{
    public static function getName(): string
    {
        return 'MTurk';
    }

    public static function getDescription(): ?string
    {
        return 'Detects respondents via the assignmentId query parameter. Generates a code to display on the thank-you screen.';
    }

    public static function getDetectParams(): array
    {
        return ['assignmentId'];
    }

    public static function getDefaults(): array
    {
        return [
            'redirect_url' => '',
            'passthrough_params' => false,
            'code_type' => 'dynamic',
            'static_code' => '',
            'code_param_key' => '',
        ];
    }

    public static function getConfigSchema(): array
    {
        return [
            ...static::codeFields(defaultType: 'dynamic'),
            static::redirectUrlField()
                ->helperText('Optional. Leave blank to display the code on the thank-you screen instead.'),
        ];
    }
}
