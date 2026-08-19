<?php

namespace BlackpigCreatif\Confessionnal\Enums;

use Filament\Support\Contracts\HasLabel;

enum FormMode: string implements HasLabel
{
    case CONVERSATIONAL = 'conversational';
    case STANDARD = 'standard';

    public function getLabel(): string
    {
        return match ($this) {
            self::CONVERSATIONAL => 'Conversational',
            self::STANDARD => 'Standard',
        };
    }
}
