<?php

namespace Modules\CostCenterManagement\Filament\Resources;

use Modules\CostCenterManagement\Filament\Resources\CostPoolResource\Pages;
use Modules\CostCenterManagement\Models\CostPool;
use Modules\CostCenterManagement\Models\CostCenter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CostPoolResource extends Resource
{
    protected static ?string $model = CostPool::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $navigationLabel = 'Cost Pools';

    protected static ?string $modelLabel = 'Cost Pool';

    protected static ?string $pluralModelLabel = 'Cost Pools';

    protected static ?string $navigationGroup = 'Cost Center Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Kode Cost Pool')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->placeholder('CP-001')
                            ->helperText('Kode unik untuk cost pool'),

                        Forms\Components\TextInput::make('name')
                            ->label('Nama Cost Pool')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Pool Biaya Listrik'),

                        Forms\Components\Select::make('pool_type')
                            ->label('Tipe Pool')
                            ->required()
                            ->options([
                                'utilities' => 'Utilities (Listrik, Air, Gas)',
                                'facility' => 'Facility (Gedung, Maintenance)',
                                'it_services' => 'IT Services',
                                'hr_services' => 'HR Services',
                                'finance_services' => 'Finance Services',
                                'other' => 'Other',
                            ])
                            ->native(false)
                            ->helperText('Kategori cost pool'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Metode Alokasi')
                    ->schema([
                        Forms\Components\Select::make('allocation_base')
                            ->label('Dasar Alokasi')
                            ->required()
                            ->options([
                                'square_footage' => 'Square Footage (Luas Ruangan)',
                                'headcount' => 'Headcount (Jumlah Pegawai)',
                                'service_volume' => 'Service Volume (Volume Layanan)',
                                'revenue' => 'Revenue (Pendapatan)',
                                'equal' => 'Equal (Sama Rata)',
                            ])
                            ->native(false)
                            ->helperText('Metode yang akan digunakan untuk alokasi pool'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->helperText('Cost pool yang tidak aktif tidak akan mengakumulasi biaya'),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->maxLength(65535)
                            ->placeholder('Deskripsi cost pool...')
                            ->helperText('Penjelasan tentang cost pool ini')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Cost Center Members')
                    ->schema([
                        Forms\Components\Repeater::make('members')
                            ->label('Cost Center Members')
                            ->relationship('members')
                            ->schema([
                                Forms\Components\Select::make('cost_center_id')
                                    ->label('Cost Center')
                                    ->required()
                                    ->options(function () {
                                        return CostCenter::active()
                                            ->orderBy('name')
                                            ->get()
                                            ->mapWithKeys(fn ($cc) => [$cc->id => "{$cc->code} - {$cc->name}"]);
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->columnSpan(2),

                                Forms\Components\Select::make('is_contributor')
                                    ->label('Role')
                                    ->required()
                                    ->options([
                                        true => 'Contributor (Kontributor)',
                                        false => 'Target (Penerima Alokasi)',
                                    ])
                                    ->default(true)
                                    ->native(false)
                                    ->helperText('Contributor = cost center yang biayanya dikumpulkan, Target = cost center yang menerima alokasi'),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Cost Center')
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                $state['cost_center_id'] 
                                    ? CostCenter::find($state['cost_center_id'])?->name . ' (' . ($state['is_contributor'] ? 'Contributor' : 'Target') . ')'
                                    : null
                            ),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\BadgeColumn::make('pool_type')
                    ->label('Tipe Pool')
                    ->colors([
                        'primary' => 'utilities',
                        'success' => 'facility',
                        'info' => 'it_services',
                        'warning' => 'hr_services',
                        'danger' => 'finance_services',
                        'secondary' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'utilities' => 'Utilities',
                        'facility' => 'Facility',
                        'it_services' => 'IT Services',
                        'hr_services' => 'HR Services',
                        'finance_services' => 'Finance Services',
                        'other' => 'Other',
                        default => $state,
                    }),

                Tables\Columns\BadgeColumn::make('allocation_base')
                    ->label('Dasar Alokasi')
                    ->colors([
                        'success' => 'equal',
                        'info' => fn ($state) => in_array($state, ['square_footage', 'headcount', 'service_volume', 'revenue']),
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'square_footage' => 'Square Footage',
                        'headcount' => 'Headcount',
                        'service_volume' => 'Service Volume',
                        'revenue' => 'Revenue',
                        'equal' => 'Equal',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('members_count')
                    ->label('Jumlah Members')
                    ->counts('members')
                    ->sortable(),

                Tables\Columns\TextColumn::make('contributors_count')
                    ->label('Contributors')
                    ->counts('contributors')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('targets_count')
                    ->label('Targets')
                    ->counts('targets')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('pool_type')
                    ->label('Tipe Pool')
                    ->options([
                        'utilities' => 'Utilities',
                        'facility' => 'Facility',
                        'it_services' => 'IT Services',
                        'hr_services' => 'HR Services',
                        'finance_services' => 'Finance Services',
                        'other' => 'Other',
                    ])
                    ->native(false),

                Tables\Filters\SelectFilter::make('allocation_base')
                    ->label('Dasar Alokasi')
                    ->options([
                        'square_footage' => 'Square Footage',
                        'headcount' => 'Headcount',
                        'service_volume' => 'Service Volume',
                        'revenue' => 'Revenue',
                        'equal' => 'Equal',
                    ])
                    ->native(false),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('code', 'asc');
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
            'index' => Pages\ListCostPools::route('/'),
            'create' => Pages\CreateCostPool::route('/create'),
            'view' => Pages\ViewCostPool::route('/{record}'),
            'edit' => Pages\EditCostPool::route('/{record}/edit'),
        ];
    }
}
