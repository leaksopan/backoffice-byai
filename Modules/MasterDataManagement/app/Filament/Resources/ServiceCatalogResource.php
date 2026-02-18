<?php

namespace Modules\MasterDataManagement\Filament\Resources;

use Modules\MasterDataManagement\Filament\Resources\ServiceCatalogResource\Pages;
use Modules\MasterDataManagement\Models\MdmServiceCatalog;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceCatalogResource extends Resource
{
    protected static ?string $model = MdmServiceCatalog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Service Catalog';

    protected static ?string $modelLabel = 'Service';

    protected static ?string $pluralModelLabel = 'Service Catalog';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Service Information')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->label('Service Code')
                            ->placeholder('e.g., SVC-001'),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Service Name')
                            ->placeholder('e.g., Konsultasi Dokter Umum'),

                        Forms\Components\Select::make('category')
                            ->required()
                            ->options([
                                'rawat_jalan' => 'Rawat Jalan',
                                'rawat_inap' => 'Rawat Inap',
                                'igd' => 'IGD',
                                'penunjang_medis' => 'Penunjang Medis',
                                'tindakan' => 'Tindakan',
                                'operasi' => 'Operasi',
                                'persalinan' => 'Persalinan',
                                'administrasi' => 'Administrasi',
                            ])
                            ->label('Category')
                            ->native(false)
                            ->searchable(),

                        Forms\Components\Select::make('unit_id')
                            ->required()
                            ->label('Unit Penyedia')
                            ->options(MdmOrganizationUnit::active()->pluck('name', 'id'))
                            ->searchable()
                            ->native(false)
                            ->preload(),

                        Forms\Components\TextInput::make('inacbg_code')
                            ->label('INA-CBG Code')
                            ->maxLength(50)
                            ->placeholder('e.g., A-4-10-I')
                            ->helperText('Kode INA-CBG jika applicable'),

                        Forms\Components\TextInput::make('standard_duration')
                            ->label('Standard Duration (minutes)')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('minutes')
                            ->placeholder('e.g., 30')
                            ->helperText('Durasi standar layanan untuk scheduling'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Service Name')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'rawat_jalan' => 'success',
                        'rawat_inap' => 'info',
                        'igd' => 'danger',
                        'penunjang_medis' => 'warning',
                        'tindakan' => 'primary',
                        'operasi' => 'purple',
                        'persalinan' => 'pink',
                        'administrasi' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'rawat_jalan' => 'Rawat Jalan',
                        'rawat_inap' => 'Rawat Inap',
                        'igd' => 'IGD',
                        'penunjang_medis' => 'Penunjang Medis',
                        'tindakan' => 'Tindakan',
                        'operasi' => 'Operasi',
                        'persalinan' => 'Persalinan',
                        'administrasi' => 'Administrasi',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit.name')
                    ->label('Unit')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('inacbg_code')
                    ->label('INA-CBG')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('standard_duration')
                    ->label('Duration')
                    ->suffix(' min')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Category')
                    ->options([
                        'rawat_jalan' => 'Rawat Jalan',
                        'rawat_inap' => 'Rawat Inap',
                        'igd' => 'IGD',
                        'penunjang_medis' => 'Penunjang Medis',
                        'tindakan' => 'Tindakan',
                        'operasi' => 'Operasi',
                        'persalinan' => 'Persalinan',
                        'administrasi' => 'Administrasi',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('unit_id')
                    ->label('Unit')
                    ->options(MdmOrganizationUnit::active()->pluck('name', 'id'))
                    ->searchable()
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All')
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('code');
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
            'index' => Pages\ListServiceCatalogs::route('/'),
            'create' => Pages\CreateServiceCatalog::route('/create'),
            'edit' => Pages\EditServiceCatalog::route('/{record}/edit'),
        ];
    }
}
