<?php

namespace BlackpigCreatif\Confessionnal\Filament\Resources\FormResource\RelationManagers;

use BlackpigCreatif\Confessionnal\Exports\SubmissionCsvExport;
use Filament\Actions;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class SubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'submissions';

    protected static ?string $title = 'Submissions';

    public function table(Table $table): Table
    {
        // Build dynamic answer columns from the form's fields
        $answerColumns = $this->buildAnswerColumns();

        return $table
            ->columns([
                Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                ...$answerColumns,
                Columns\TextColumn::make('locale')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                Columns\TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('In progress'),
                Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Actions\Action::make('export_csv')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (): \Symfony\Component\HttpFoundation\StreamedResponse {
                        $form = $this->getOwnerRecord();

                        return SubmissionCsvExport::download($form);
                    }),
            ])
            ->actions([
                Actions\Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Submission Details')
                    ->modalContent(fn ($record) => view('confessionnal::filament.submission-detail', [
                        'submission' => $record,
                        'fields' => $this->getFormFields(),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function buildAnswerColumns(): array
    {
        $fields = $this->getFormFields();
        $columns = [];

        foreach ($fields->take(5) as $field) {
            $columns[] = Columns\TextColumn::make("answers.{$field->key}")
                ->label($field->getTranslation('label', app()->getLocale()))
                ->limit(50)
                ->toggleable()
                ->formatStateUsing(function (mixed $state): ?string {
                    if (is_array($state)) {
                        return implode(', ', $state);
                    }

                    return $state;
                });
        }

        return $columns;
    }

    protected function getFormFields(): Collection
    {
        return $this->getOwnerRecord()
            ->sections()
            ->with(['pages.fields'])
            ->get()
            ->flatMap(fn ($section) => $section->pages)
            ->flatMap(fn ($page) => $page->fields);
    }
}
