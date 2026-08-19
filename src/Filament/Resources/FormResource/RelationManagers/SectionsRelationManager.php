<?php

namespace BlackpigCreatif\Confessionnal\Filament\Resources\FormResource\RelationManagers;

use BlackpigCreatif\Confessionnal\Enums\FieldType;
use BlackpigCreatif\Confessionnal\Filament\Resources\FormResource;
use Filament\Actions;
use Filament\Forms\Components\Repeater;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sections';

    protected static ?string $title = 'Sections';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Section details')
                ->schema([
                    TextInput::make('title')
                        ->formatStateUsing(fn ($state) => FormResource::resolveTranslatable($state)),
                    Textarea::make('subtext')
                        ->rows(2)
                        ->formatStateUsing(fn ($state) => FormResource::resolveTranslatable($state)),
                    TextInput::make('randomisation_group')
                        ->helperText('Sections sharing a group value are shuffled per respondent'),
                ])
                ->columnSpanFull(),

            Section::make('Pages & Fields')
                ->schema([
                    Repeater::make('pages')
                        ->relationship()
                        ->orderColumn('sort_order')
                        ->schema([
                            Repeater::make('fields')
                                ->relationship()
                                ->orderColumn('sort_order')
                                ->mutateRelationshipDataBeforeFillUsing(function (array $data): array {
                                    $options = $data['options'] ?? [];

                                    if (is_array($options) && ! array_is_list($options)) {
                                        $data['scale_min'] = $options['min'] ?? 1;
                                        $data['scale_max'] = $options['max'] ?? 5;
                                        $data['scale_label_low'] = $options['label_low'] ?? null;
                                        $data['scale_label_mid'] = $options['label_mid'] ?? null;
                                        $data['scale_label_high'] = $options['label_high'] ?? null;
                                    }

                                    return $data;
                                })
                                ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                    return static::packScaleOptions($data);
                                })
                                ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                                    return static::packScaleOptions($data);
                                })
                                ->schema([
                                    Grid::make(3)
                                        ->schema([
                                            Select::make('type')
                                                ->options(FieldType::class)
                                                ->required()
                                                ->live(),
                                            TextInput::make('key')
                                                ->required()
                                                ->alphaDash()
                                                ->helperText('Auto-generated from label.'),
                                            Toggle::make('is_required')
                                                ->inline(false),
                                        ]),
                                    TextInput::make('label')
                                        ->required()
                                        ->live(onBlur: true)
                                        ->formatStateUsing(fn ($state) => FormResource::resolveTranslatable($state))
                                        ->afterStateUpdated(fn (Set $set, ?string $state) => $set('key', Str::slug($state ?? '', '_'))),
                                    TextInput::make('placeholder')
                                        ->formatStateUsing(fn ($state) => FormResource::resolveTranslatable($state)),
                                    Textarea::make('help_text')
                                        ->rows(2)
                                        ->formatStateUsing(fn ($state) => FormResource::resolveTranslatable($state)),
                                    Repeater::make('options')
                                        ->schema([
                                            TextInput::make('label')
                                                ->required(),
                                            TextInput::make('value')
                                                ->required(),
                                        ])
                                        ->columns(2)
                                        ->collapsible()
                                        ->defaultItems(0)
                                        ->addActionLabel('Add option')
                                        ->visible(function (Get $get): bool {
                                            $type = $get('type');

                                            if ($type instanceof FieldType) {
                                                $type = $type->value;
                                            }

                                            return in_array($type, [
                                                FieldType::SELECT->value,
                                                FieldType::RADIO->value,
                                                FieldType::CHECKBOX->value,
                                            ]);
                                        }),
                                    Section::make('Scale configuration')
                                        ->schema([
                                            Grid::make(2)
                                                ->schema([
                                                    TextInput::make('scale_min')
                                                        ->label('Min value')
                                                        ->numeric()
                                                        ->default(1)
                                                        ->required(),
                                                    TextInput::make('scale_max')
                                                        ->label('Max value')
                                                        ->numeric()
                                                        ->default(5)
                                                        ->required(),
                                                ]),
                                            Grid::make(3)
                                                ->schema([
                                                    TextInput::make('scale_label_low')
                                                        ->label('Low label')
                                                        ->placeholder('e.g. Not at all'),
                                                    TextInput::make('scale_label_mid')
                                                        ->label('Mid label')
                                                        ->placeholder('e.g. Average'),
                                                    TextInput::make('scale_label_high')
                                                        ->label('High label')
                                                        ->placeholder('e.g. Excellent'),
                                                ]),
                                        ])
                                        ->visible(function (Get $get): bool {
                                            $type = $get('type');

                                            if ($type instanceof FieldType) {
                                                $type = $type->value;
                                            }

                                            return $type === FieldType::SCALE->value;
                                        }),
                                    Section::make('Conditional logic')
                                        ->schema([
                                            Select::make('conditional_logic.field_key')
                                                ->label('Show when field')
                                                ->options(function ($livewire): array {
                                                    return $this->getFormFieldOptions($livewire);
                                                })
                                                ->searchable(),
                                            Select::make('conditional_logic.operator')
                                                ->label('Operator')
                                                ->options([
                                                    'equals' => 'Equals',
                                                    'not_equals' => 'Does not equal',
                                                    'contains' => 'Contains',
                                                    'not_contains' => 'Does not contain',
                                                    'is_filled' => 'Is filled',
                                                    'is_empty' => 'Is empty',
                                                ]),
                                            TextInput::make('conditional_logic.value')
                                                ->label('Value')
                                                ->helperText('Leave blank for is_filled / is_empty.'),
                                        ])
                                        ->columns(3)
                                        ->collapsed()
                                        ->collapsible(),
                                    TagsInput::make('validation_rules')
                                        ->helperText('Laravel validation rules (e.g. email, min:3, max:255)')
                                        ->placeholder('Add rule'),
                                ])
                                ->collapsed()
                                ->collapsible()
                                ->cloneable()
                                ->itemLabel(function (array $state): ?string {
                                    $label = $state['label'] ?? null;

                                    if (is_array($label)) {
                                        $label = $label[app()->getLocale()] ?? $label[array_key_first($label)] ?? null;
                                    }

                                    $type = $state['type'] ?? null;

                                    if ($type instanceof FieldType) {
                                        $typeLabel = $type->getLabel();
                                    } elseif (is_string($type)) {
                                        $typeLabel = FieldType::tryFrom($type)?->getLabel() ?? $type;
                                    } else {
                                        $typeLabel = null;
                                    }

                                    if ($typeLabel && $label) {
                                        return "{$typeLabel}: {$label}";
                                    }

                                    return $label ?? $typeLabel;
                                })
                                ->addActionLabel('Add field'),
                        ])
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => 'Page')
                        ->addActionLabel('Add page'),
                ])
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('title')
                    ->placeholder('(untitled)'),
                Columns\TextColumn::make('pages_count')
                    ->counts('pages')
                    ->label('Pages'),
                Columns\TextColumn::make('randomisation_group')
                    ->label('Group')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->headerActions([
                Actions\CreateAction::make()
                    ->slideOver(),
            ])
            ->actions([
                Actions\EditAction::make()
                    ->slideOver(),
                Actions\Action::make('clone')
                    ->label('Clone')
                    ->icon('heroicon-o-document-duplicate')
                    ->requiresConfirmation()
                    ->action(function (\BlackpigCreatif\Confessionnal\Models\Section $record) {
                        $clone = $record->replicate(['id', 'pages_count']);
                        $clone->title = $record->getTranslation('title', app()->getLocale()) . ' (copy)';
                        $clone->sort_order = $record->form->sections()->max('sort_order') + 1;
                        $clone->save();

                        $record->load('pages.fields');

                        foreach ($record->pages as $page) {
                            $clonedPage = $clone->pages()->create(
                                $page->only(['sort_order']),
                            );

                            foreach ($page->fields as $field) {
                                $clonedPage->fields()->create(
                                    collect($field->toArray())
                                        ->except(['id', 'form_page_id', 'created_at', 'updated_at'])
                                        ->toArray(),
                                );
                            }
                        }

                        Notification::make()
                            ->title('Section cloned')
                            ->success()
                            ->send();
                    }),
                Actions\DeleteAction::make(),
            ]);
    }

    protected static function packScaleOptions(array $data): array
    {
        $type = $data['type'] ?? null;

        if ($type instanceof FieldType) {
            $type = $type->value;
        }

        if ($type === FieldType::SCALE->value) {
            $data['options'] = [
                'min' => (int) ($data['scale_min'] ?? 1),
                'max' => (int) ($data['scale_max'] ?? 5),
                'label_low' => $data['scale_label_low'] ?? null,
                'label_mid' => $data['scale_label_mid'] ?? null,
                'label_high' => $data['scale_label_high'] ?? null,
            ];
        }

        unset($data['scale_min'], $data['scale_max'], $data['scale_label_low'], $data['scale_label_mid'], $data['scale_label_high']);

        return $data;
    }

    protected function getFormFieldOptions($livewire): array
    {
        $form = $livewire->getOwnerRecord();

        if (! $form) {
            return [];
        }

        return $form->sections()
            ->with('pages.fields')
            ->get()
            ->flatMap(fn ($s) => $s->pages)
            ->flatMap(fn ($p) => $p->fields)
            ->mapWithKeys(fn ($f) => [
                $f->key => $f->getTranslation('label', app()->getLocale()) . " ({$f->key})",
            ])
            ->toArray();
    }
}
