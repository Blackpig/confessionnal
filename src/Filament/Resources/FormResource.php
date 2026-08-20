<?php

namespace BlackpigCreatif\Confessionnal\Filament\Resources;

use BlackpigCreatif\Confessionnal\Contracts\CanReceiveSubmissions;
use BlackpigCreatif\Confessionnal\Enums\FormMode;
use BlackpigCreatif\Confessionnal\Filament\Resources\FormResource\Pages;
use BlackpigCreatif\Confessionnal\Filament\Resources\FormResource\RelationManagers;
use BlackpigCreatif\Confessionnal\Filament\Resources\FormResource\Widgets;
use BlackpigCreatif\Confessionnal\Models\Form;
use BlackpigCreatif\Confessionnal\Support\ModelDiscovery;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class FormResource extends Resource
{
    protected static ?string $model = Form::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static string | \UnitEnum | null $navigationGroup = 'Confessionnal';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Form')
                ->tabs([
                    Tabs\Tab::make('General')
                        ->schema([
                            TextInput::make('name')
                                ->required()
                                ->live(onBlur: true)
                                ->formatStateUsing(fn ($state) => static::resolveTranslatable($state))
                                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                            TextInput::make('slug')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->alphaDash(),
                            Textarea::make('description')
                                ->rows(3)
                                ->formatStateUsing(fn ($state) => static::resolveTranslatable($state)),
                            Select::make('mode')
                                ->options(FormMode::class)
                                ->required()
                                ->default(FormMode::CONVERSATIONAL),
                        ]),
                    Tabs\Tab::make('Display')
                        ->schema([
                            Toggle::make('settings.show_progress')
                                ->label('Show progress bar')
                                ->helperText('Display the progress bar and section indicator during form fill.')
                                ->default(true)
                                ->formatStateUsing(fn ($state) => $state ?? true),
                        ]),
                    Tabs\Tab::make('Publishing')
                        ->schema([
                            Toggle::make('is_published')
                                ->label('Published'),
                            DateTimePicker::make('published_at')
                                ->label('Publish date'),
                        ]),
                    Tabs\Tab::make('Completion')
                        ->schema([
                            TextInput::make('settings.redirect_url')
                                ->label('Redirect URL')
                                ->url()
                                ->helperText('Redirect respondent here after submission instead of showing thank-you screen. Leave blank for default.'),
                            Toggle::make('settings.passthrough_params')
                                ->label('Pass query params to redirect URL')
                                ->helperText('Append captured query params to the redirect URL.'),
                        ]),
                    Tabs\Tab::make('Query Capture')
                        ->schema([
                            Select::make('settings.query_capture_mode')
                                ->label('Capture mode')
                                ->options([
                                    'none' => 'None',
                                    'all' => 'Capture all query params',
                                    'whitelist' => 'Capture specific params only',
                                ])
                                ->default('none')
                                ->live(),
                            TagsInput::make('settings.query_capture_whitelist')
                                ->label('Allowed params')
                                ->helperText('e.g. PROLIFIC_PID, STUDY_ID, utm_source')
                                ->placeholder('Add param name')
                                ->visible(fn (Get $get): bool => $get('settings.query_capture_mode') === 'whitelist'),
                        ]),
                    Tabs\Tab::make('Target Model')
                        ->schema([
                            Select::make('settings.target_model')
                                ->label('Model class')
                                ->options(fn () => ModelDiscovery::discover())
                                ->searchable()
                                ->live()
                                ->helperText('Models implementing CanReceiveSubmissions. Leave blank for generic submissions only.'),
                            Select::make('settings.target_mode')
                                ->label('Write mode')
                                ->options([
                                    'create' => 'Create new record',
                                    'update' => 'Update or create (upsert)',
                                ])
                                ->default('create')
                                ->live(),
                            Select::make('settings.target_find_by')
                                ->label('Find by column')
                                ->options(function (Get $get): array {
                                    $modelClass = $get('settings.target_model');

                                    if (! $modelClass || ! is_subclass_of($modelClass, CanReceiveSubmissions::class)) {
                                        return [];
                                    }

                                    return $modelClass::getMappableColumns();
                                })
                                ->searchable()
                                ->helperText('Column to match on when using upsert mode.')
                                ->visible(fn (Get $get): bool => $get('settings.target_mode') === 'update'),
                            Repeater::make('settings.field_mapping')
                                ->label('Field mapping')
                                ->schema([
                                    Select::make('field_key')
                                        ->label('Form field')
                                        ->options(function (?Form $record): array {
                                            if (! $record) {
                                                return [];
                                            }

                                            return $record->sections()
                                                ->with('pages.fields')
                                                ->get()
                                                ->flatMap(fn ($s) => $s->pages)
                                                ->flatMap(fn ($p) => $p->fields)
                                                ->mapWithKeys(fn ($f) => [
                                                    $f->key => $f->getTranslation('label', app()->getLocale()),
                                                ])
                                                ->toArray();
                                        })
                                        ->searchable()
                                        ->required(),
                                    Select::make('model_column')
                                        ->label('Model column')
                                        ->options(function (Get $get, $livewire): array {
                                            $modelClass = $livewire->data['settings']['target_model'] ?? null;

                                            if (! $modelClass || ! class_exists($modelClass) || ! is_subclass_of($modelClass, CanReceiveSubmissions::class)) {
                                                return [];
                                            }

                                            return $modelClass::getMappableColumns();
                                        })
                                        ->searchable()
                                        ->required(),
                                ])
                                ->columns(2)
                                ->addActionLabel('Add mapping')
                                ->defaultItems(0)
                                ->visible(fn (Get $get): bool => filled($get('settings.target_model'))),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('name')
                    ->searchable(),
                Columns\TextColumn::make('slug')
                    ->toggleable(),
                Columns\TextColumn::make('mode')
                    ->badge(),
                Columns\IconColumn::make('is_published')
                    ->boolean()
                    ->label('Published'),
                Columns\TextColumn::make('submissions_count')
                    ->counts('submissions')
                    ->label('Submissions'),
                Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('updated_at', 'desc')
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function resolveTranslatable(mixed $state): ?string
    {
        if (is_array($state)) {
            return $state[app()->getLocale()] ?? $state[array_key_first($state)] ?? null;
        }

        return $state;
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\FormAnalyticsOverview::class,
        ];
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SectionsRelationManager::class,
            RelationManagers\SubmissionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListForms::route('/'),
            'create' => Pages\CreateForm::route('/create'),
            'edit' => Pages\EditForm::route('/{record}/edit'),
        ];
    }
}
