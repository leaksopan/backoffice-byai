<?php

namespace Modules\MasterDataManagement\Filament\Resources;

use Modules\MasterDataManagement\Filament\Resources\OrganizationUnitResource\Pages;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrganizationUnitResource extends Resource
{
    protected static ?string $model = MdmOrganizationUnit::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Organization Units';

    protected static ?string $modelLabel = 'Organization Unit';

    protected static ?string $pluralModelLabel = 'Organization Units';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->label('Unit Code')
                            ->placeholder('e.g., ORG001'),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Unit Name')
                            ->placeholder('e.g., Instalasi Rawat Jalan'),

                        Forms\Components\Select::make('type')
                            ->required()
                            ->options([
                                'installation' => 'Installation',
                                'department' => 'Department',
                                'unit' => 'Unit',
                                'section' => 'Section',
                            ])
                            ->label('Unit Type')
                            ->native(false),

                        Forms\Components\Select::make('parent_id')
                            ->label('Parent Unit')
                            ->options(function () {
                                return static::getOrganizationTreeOptions();
                            })
                            ->searchable()
                            ->nullable()
                            ->helperText('Select parent unit to create hierarchy')
                            ->native(false),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->label('Code'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Name')
                    ->description(fn (MdmOrganizationUnit $record): string => 
                        $record->hierarchy_path ? "Path: {$record->hierarchy_path}" : ''
                    ),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'installation' => 'success',
                        'department' => 'info',
                        'unit' => 'warning',
                        'section' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Parent Unit')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('level')
                    ->label('Level')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('hierarchy_path', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'installation' => 'Installation',
                        'department' => 'Department',
                        'unit' => 'Unit',
                        'section' => 'Section',
                    ])
                    ->label('Unit Type'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All units')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Parent Unit')
                    ->options(fn () => MdmOrganizationUnit::query()
                        ->whereNull('parent_id')
                        ->pluck('name', 'id')
                    )
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (MdmOrganizationUnit $record) {
                        if ($record->children()->exists()) {
                            throw new \Exception('Cannot delete unit with child units. Please remove or reassign child units first.');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->children()->exists()) {
                                    throw new \Exception("Cannot delete unit '{$record->name}' with child units.");
                                }
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrganizationUnits::route('/'),
            'create' => Pages\CreateOrganizationUnit::route('/create'),
            'view' => Pages\ViewOrganizationUnit::route('/{record}'),
            'edit' => Pages\EditOrganizationUnit::route('/{record}/edit'),
        ];
    }

    /**
     * Get organization tree options for parent selector
     * Returns hierarchical list with indentation
     */
    protected static function getOrganizationTreeOptions(): array
    {
        $units = MdmOrganizationUnit::query()
            ->orderBy('hierarchy_path')
            ->get();

        $options = [];
        foreach ($units as $unit) {
            $indent = str_repeat('— ', $unit->level);
            $options[$unit->id] = $indent . $unit->name . ' (' . $unit->code . ')';
        }

        return $options;
    }

    /**
     * Get navigation badge (count of active units)
     */
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }

    /**
     * Get navigation badge color
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}

