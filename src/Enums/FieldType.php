<?php

namespace BlackpigCreatif\Confessionnal\Enums;

use Filament\Support\Contracts\HasLabel;

enum FieldType: string implements HasLabel
{
    case TEXT = 'text';
    case TEXTAREA = 'textarea';
    case SELECT = 'select';
    case RADIO = 'radio';
    case CHECKBOX = 'checkbox';
    case SCALE = 'scale';
    case DATE = 'date';
    case FILE_UPLOAD = 'file_upload';

    public function getLabel(): string
    {
        return match ($this) {
            self::TEXT => 'Short Text',
            self::TEXTAREA => 'Long Text',
            self::SELECT => 'Dropdown',
            self::RADIO => 'Radio Buttons',
            self::CHECKBOX => 'Checkboxes',
            self::SCALE => 'Scale',
            self::DATE => 'Date',
            self::FILE_UPLOAD => 'File Upload',
        };
    }
}
