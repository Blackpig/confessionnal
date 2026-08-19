<?php

namespace BlackpigCreatif\Confessionnal\Atelier;

use BlackpigCreatif\Atelier\Abstracts\BaseBlock;
use BlackpigCreatif\Confessionnal\Models\Form;
use Filament\Forms\Components\Select;
use Illuminate\Contracts\View\View;

class ConfessionnalFormBlock extends BaseBlock
{
    public static function getLabel(): string
    {
        return 'Form (Confessionnal)';
    }

    public static function getDescription(): ?string
    {
        return 'Embed a Confessionnal form on the page.';
    }

    public static function getIcon(): string
    {
        return 'heroicon-o-document-text';
    }

    public static function getSchema(): array
    {
        return [
            static::getPublishedField(),

            Select::make('form_id')
                ->label('Form')
                ->options(fn (): array => Form::where('is_published', true)
                    ->get()
                    ->mapWithKeys(fn (Form $form) => [
                        $form->id => $form->getTranslation('name', app()->getLocale()),
                    ])
                    ->toArray())
                ->required()
                ->searchable(),

            ...static::getCommonOptionsSchema(),
        ];
    }

    public static function getViewPath(): string
    {
        return 'confessionnal::atelier.form-block';
    }

    public function render(): View
    {
        $form = Form::find($this->get('form_id'));

        return view(static::getViewPath(), array_merge($this->getViewData(), [
            'form' => $form,
            'slug' => $form?->slug,
        ]));
    }
}
