<?php

namespace Modules\MasterDataManagement\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Modules\MasterDataManagement\Models\MdmHumanResource;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use Modules\MasterDataManagement\Filament\Resources\MdmHumanResourceResource\Pages;
use Modules\MasterDataManagement\Filament\Resources\MdmHumanResourceResource\RelationManagers;

class MdmHumanResourceResource extends Resource
{
    protected static ?string $model = MdmHumanResource::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'SDM';

    protected static ?string $modelLabel = 'SDM';

    protected static ?string $pluralModelLabel = 'Data SDM';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\TextInput::make('nip')
                            ->label('NIP')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                        
                        Forms\Components\TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Select::make('category')
                            ->label('Kategori')
                            ->required()
                            ->options([
                                'medis_dokter' => 'Medis - Dokter',
                                'medis_perawat' => 'Medis - Perawat',
                                'medis_bidan' => 'Medis - Bidan',
                                'penunjang_medis' => 'Penunjang Medis',
                                'administrasi' => 'Administrasi',
                                'umum' => 'Umum',
                            ]),
                        
                        Forms\Components\TextInput::make('position')
                            ->label('Jabatan')
                            ->required()
                            ->maxLength(100),
                    ])->columns(2),

                Forms\Components\Section::make('Status Kepegawaian')
                    ->schema([
                        Forms\Components\Select::make('employment_status')
                            ->label('Status Kepegawaian')
                            ->required()
                            ->options([
                                'pns' => 'PNS',
                                'pppk' => 'PPPK',
                                'kontrak' => 'Kontrak',
                                'honorer' => 'Honorer',
                            ]),
                        
                        Forms\Components\TextInput::make('grade')
                            ->label('Golongan')
                            ->maxLength(10),
                        
                        Forms\Components\TextInput::make('basic_salary')
                            ->label('Gaji Pokok')
                            ->numeric()
                            ->prefix('Rp'),
                        
                        Forms\Components\TextInput::make('effective_hours_per_week')
                            ->label('Jam Kerja Efektif per Minggu')
                            ->numeric()
                            ->suffix('jam'),
                    ])->columns(2),

                Forms\Components\Section::make('Tanggal')
                    ->schema([
                        Forms\Components\DatePicker::make('hire_date')
                            ->label('Tanggal Masuk'),
                        
                        Forms\Components\DatePicker::make('termination_date')
                            ->label('Tanggal Keluar'),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'medis_dokter' => 'Dokter',
                        'medis_perawat' => 'Perawat',
                        'medis_bidan' => 'Bidan',
                        'penunjang_medis' => 'Penunjang Medis',
                        'administrasi' => 'Administrasi',
                        'umum' => 'Umum',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'medis_dokter' => 'success',
                        'medis_perawat' => 'info',
                        'medis_bidan' => 'warning',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('position')
                    ->label('Jabatan')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('employment_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'medis_dokter' => 'Medis - Dokter',
                        'medis_perawat' => 'Medis - Perawat',
                        'medis_bidan' => 'Medis - Bidan',
                        'penunjang_medis' => 'Penunjang Medis',
                        'administrasi' => 'Administrasi',
                        'umum' => 'Umum',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('assignments')
                    ->label('Penugasan')
                    ->icon('heroicon-o-briefcase')
                    ->url(fn (MdmHumanResource $record): string => 
                        route('mdm.human-resources.assignments', $record)
                    ),
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
            RelationManagers\AssignmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMdmHumanResources::route('/'),
            'create' => Pages\CreateMdmHumanResource::route('/create'),
            'edit' => Pages\EditMdmHumanResource::route('/{record}/edit'),
        ];
    }
}
