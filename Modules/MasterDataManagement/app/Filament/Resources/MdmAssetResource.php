<?php

namespace Modules\MasterDataManagement\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Modules\MasterDataManagement\Models\MdmAsset;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use Modules\MasterDataManagement\Filament\Resources\MdmAssetResource\Pages;
use Modules\MasterDataManagement\Filament\Resources\MdmAssetResource\RelationManagers;

class MdmAssetResource extends Resource
{
    protected static ?string $model = MdmAsset::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Aset';

    protected static ?string $modelLabel = 'Aset';

    protected static ?string $pluralModelLabel = 'Data Aset';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Kode Aset')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                        
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Aset')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Select::make('category')
                            ->label('Kategori')
                            ->required()
                            ->options([
                                'tanah' => 'Tanah',
                                'gedung' => 'Gedung',
                                'peralatan_medis' => 'Peralatan Medis',
                                'peralatan_non_medis' => 'Peralatan Non-Medis',
                                'kendaraan' => 'Kendaraan',
                                'inventaris' => 'Inventaris',
                            ]),
                        
                        Forms\Components\Select::make('condition')
                            ->label('Kondisi')
                            ->required()
                            ->options([
                                'baik' => 'Baik',
                                'rusak_ringan' => 'Rusak Ringan',
                                'rusak_berat' => 'Rusak Berat',
                            ])
                            ->default('baik'),
                    ])->columns(2),

                Forms\Components\Section::make('Informasi Keuangan')
                    ->schema([
                        Forms\Components\TextInput::make('acquisition_value')
                            ->label('Nilai Perolehan')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        
                        Forms\Components\DatePicker::make('acquisition_date')
                            ->label('Tanggal Perolehan')
                            ->required(),
                        
                        Forms\Components\TextInput::make('residual_value')
                            ->label('Nilai Residu')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                    ])->columns(3),

                Forms\Components\Section::make('Depresiasi')
                    ->schema([
                        Forms\Components\TextInput::make('useful_life_years')
                            ->label('Umur Ekonomis (Tahun)')
                            ->numeric()
                            ->suffix('tahun')
                            ->helperText('Kosongkan jika aset tidak didepresiasi (misal: tanah)'),
                        
                        Forms\Components\Select::make('depreciation_method')
                            ->label('Metode Depresiasi')
                            ->options([
                                'straight_line' => 'Garis Lurus',
                                'declining_balance' => 'Saldo Menurun',
                                'units_of_production' => 'Unit Produksi',
                            ])
                            ->helperText('Kosongkan jika aset tidak didepresiasi'),
                    ])->columns(2),

                Forms\Components\Section::make('Lokasi & Status')
                    ->schema([
                        Forms\Components\Select::make('current_location_id')
                            ->label('Lokasi Saat Ini')
                            ->options(MdmOrganizationUnit::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Aset')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('category')
                    ->label('Kategori')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'tanah' => 'Tanah',
                        'gedung' => 'Gedung',
                        'peralatan_medis' => 'Peralatan Medis',
                        'peralatan_non_medis' => 'Peralatan Non-Medis',
                        'kendaraan' => 'Kendaraan',
                        'inventaris' => 'Inventaris',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'tanah',
                        'info' => 'gedung',
                        'warning' => 'peralatan_medis',
                        'primary' => 'peralatan_non_medis',
                        'danger' => 'kendaraan',
                        'secondary' => 'inventaris',
                    ]),
                
                Tables\Columns\TextColumn::make('acquisition_value')
                    ->label('Nilai Perolehan')
                    ->money('IDR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('currentLocation.name')
                    ->label('Lokasi')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('condition')
                    ->label('Kondisi')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'baik' => 'Baik',
                        'rusak_ringan' => 'Rusak Ringan',
                        'rusak_berat' => 'Rusak Berat',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'baik',
                        'warning' => 'rusak_ringan',
                        'danger' => 'rusak_berat',
                    ]),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'tanah' => 'Tanah',
                        'gedung' => 'Gedung',
                        'peralatan_medis' => 'Peralatan Medis',
                        'peralatan_non_medis' => 'Peralatan Non-Medis',
                        'kendaraan' => 'Kendaraan',
                        'inventaris' => 'Inventaris',
                    ]),
                
                Tables\Filters\SelectFilter::make('current_location_id')
                    ->label('Lokasi')
                    ->options(MdmOrganizationUnit::where('is_active', true)->pluck('name', 'id'))
                    ->searchable(),
                
                Tables\Filters\SelectFilter::make('condition')
                    ->label('Kondisi')
                    ->options([
                        'baik' => 'Baik',
                        'rusak_ringan' => 'Rusak Ringan',
                        'rusak_berat' => 'Rusak Berat',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MovementsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMdmAssets::route('/'),
            'create' => Pages\CreateMdmAsset::route('/create'),
            'view' => Pages\ViewMdmAsset::route('/{record}'),
            'edit' => Pages\EditMdmAsset::route('/{record}/edit'),
        ];
    }
}
