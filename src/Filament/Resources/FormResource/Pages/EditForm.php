<?php

namespace BlackpigCreatif\Confessionnal\Filament\Resources\FormResource\Pages;

use BlackpigCreatif\Confessionnal\Filament\Resources\FormResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditForm extends EditRecord
{
    protected static string $resource = FormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
