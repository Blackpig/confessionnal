<?php

namespace BlackpigCreatif\Confessionnal\CompletionProviders;

use BlackpigCreatif\Confessionnal\Contracts\CompletionProvider;
use Filament\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

abstract class BaseCompletionProvider implements CompletionProvider
{
    public static function getDescription(): ?string
    {
        return null;
    }

    public function detect(array $queryParams, array $config): bool
    {
        foreach (static::getDetectParams() as $param) {
            if (array_key_exists($param, $queryParams)) {
                return true;
            }
        }

        return false;
    }

    public function generateCode(array $config): ?string
    {
        return match ($config['code_type'] ?? 'none') {
            'static' => $config['static_code'] ?? null,
            'dynamic' => strtoupper(substr(bin2hex(random_bytes(4)), 0, 8)),
            default => null,
        };
    }

    public function buildRedirectUrl(array $config, ?string $code, array $capturedParams): ?string
    {
        $url = $config['redirect_url'] ?? null;

        if (! $url) {
            return null;
        }

        if ($code && ! empty($config['code_param_key'])) {
            $separator = str_contains($url, '?') ? '&' : '?';
            $url .= $separator . urlencode($config['code_param_key']) . '=' . urlencode($code);
        }

        if (! empty($config['passthrough_params']) && ! empty($capturedParams)) {
            $separator = str_contains($url, '?') ? '&' : '?';
            $url .= $separator . http_build_query($capturedParams);
        }

        return $url;
    }

    protected static function redirectUrlField(?string $default = null): TextInput
    {
        $field = TextInput::make('redirect_url')
            ->label('Redirect URL')
            ->url();

        if ($default !== null) {
            $field->default($default);
        }

        return $field;
    }

    protected static function passthroughField(bool $default = false): Toggle
    {
        return Toggle::make('passthrough_params')
            ->label('Pass captured query params to redirect URL')
            ->default($default);
    }

    /** @return array<Component> */
    protected static function codeFields(string $defaultType = 'none', ?string $defaultParamKey = null): array
    {
        return [
            Select::make('code_type')
                ->label('Completion code')
                ->options([
                    'none' => 'No code',
                    'static' => 'Static (same for all respondents)',
                    'dynamic' => 'Dynamic (unique per submission)',
                ])
                ->default($defaultType)
                ->live(),
            TextInput::make('static_code')
                ->label('Code')
                ->visible(fn (Get $get): bool => $get('code_type') === 'static')
                ->required(fn (Get $get): bool => $get('code_type') === 'static')
                ->suffixAction(
                    Action::make('generateCode')
                        ->icon('heroicon-m-arrow-path')
                        ->tooltip('Generate a random code')
                        ->action(fn (Set $set) => $set('static_code', strtoupper(substr(bin2hex(random_bytes(4)), 0, 8)))),
                ),
            TextInput::make('code_param_key')
                ->label('Code URL parameter')
                ->placeholder('e.g. cc, code')
                ->helperText('Appended to the redirect URL as ?key=CODE.')
                ->default($defaultParamKey)
                ->visible(fn (Get $get): bool => in_array($get('code_type'), ['static', 'dynamic'])),
        ];
    }
}
