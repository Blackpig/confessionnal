<?php

namespace BlackpigCreatif\Confessionnal\Filament\Resources\FormResource\Pages;

use BlackpigCreatif\Confessionnal\Filament\Resources\FormResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\URL;

class EditForm extends EditRecord
{
    protected static string $resource = FormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('Preview')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->url(fn () => URL::signedRoute('confessionnal.fill', [
                    'slug' => $this->record->slug,
                    'preview' => 1,
                ]))
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }
}
