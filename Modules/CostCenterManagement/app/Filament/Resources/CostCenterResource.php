<?php

namespace Modules\CostCenterManagement\Filament\Resources;

use Modules\CostCenterManagement\Filament\Resources\CostCenterResource\Pages;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CostCenterResource extends Resource
{
    protected static ?string $model = CostCenter::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Cost Centers';

    protected static ?string $modelLabel = 'Cost Center';

    protected static ?string $pluralModelLabel = 'Cost Centers';

    protected static ?string $navigationGroup = 'Cost Center Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Kode Cost Center')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->placeholder('CC-001')
                            ->helperText('Kode unik untuk cost center'),

                        Forms\Components\TextInput::make('name')
                            ->label('Nama Cost Center')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Rawat Jalan'),

                        Forms\Components\Select::make('type')
                            ->label('Tipe Cost Center')
                            ->required()
                            ->options([
                                'medical' => 'Medical',
                                'non_medical' => 'Non-Medical',
                                'administrative' => 'Administrative',
                                'profit_center' => 'Profit Center',
                            ])
                            ->native(false)
                            ->reactive()
                            ->helperText('Pilih tipe cost center sesuai fungsi'),

                        Forms\Components\TextInput::make('classification')
                            ->label('Klasifikasi')
                            ->maxLength(100)
                            ->placeholder('Rawat Jalan, Laboratorium, Keuangan, dll')
                            ->helperText('Klasifikasi detail berdasarkan service line atau fungsi'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Struktur Organisasi')
                    ->schema([
                        Forms\Components\Select::make('organization_unit_id')
                            ->label('Unit Organisasi')
                            ->required()
                            ->options(function () {
                                return MdmOrganizationUnit::active()
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->helperText('Pilih unit organisasi dari Master Data Management'),

                        Forms\Components\Select::make('parent_id')
                            ->label('Parent Cost Center')
                            ->options(function (?CostCenter $record) {
                                $query = CostCenter::active()
                                    ->orderBy('name');
                                
                                // Exclude current record from parent options
                                if ($record) {
                                    $query->where('id', '!=', $record->id);
                                }
                                
                                return $query->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->helperText('Pilih parent cost center untuk hierarki (opsional)'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Manajemen & Status')
                    ->schema([
                        Forms\Components\Select::make('manager_user_id')
                            ->label('Cost Center Manager')
                            ->options(function () {
                                return User::orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->helperText('Pilih user yang bertanggung jawab atas cost center ini'),

                        Forms\Components\DatePicker::make('effective_date')
                            ->label('Tanggal Efektif')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->helperText('Tanggal mulai berlaku cost center'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->helperText('Cost center yang tidak aktif tidak dapat menerima transaksi baru'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Deskripsi')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->maxLength(65535)
                            ->placeholder('Deskripsi detail tentang cost center ini...')
                            ->columnSpanFull(),
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

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipe')
                    ->colors([
                        'success' => 'medical',
                        'info' => 'non_medical',
                        'warning' => 'administrative',
                        'primary' => 'profit_center',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'medical' => 'Medical',
                        'non_medical' => 'Non-Medical',
                        'administrative' => 'Administrative',
                        'profit_center' => 'Profit Center',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('classification')
                    ->label('Klasifikasi')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('organizationUnit.name')
                    ->label('Unit Organisasi')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Parent')
                    ->searchable()
                    ->toggleable()
                    ->default('-'),

                Tables\Columns\TextColumn::make('manager.name')
                    ->label('Manager')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('effective_date')
                    ->label('Tanggal Efektif')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'medical' => 'Medical',
                        'non_medical' => 'Non-Medical',
                        'administrative' => 'Administrative',
                        'profit_center' => 'Profit Center',
                    ])
                    ->native(false),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif')
                    ->native(false),

                Tables\Filters\SelectFilter::make('organization_unit_id')
                    ->label('Unit Organisasi')
                    ->options(function () {
                        return MdmOrganizationUnit::active()
                            ->orderBy('name')
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->native(false),

                Tables\Filters\Filter::make('has_parent')
                    ->label('Memiliki Parent')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('parent_id')),

                Tables\Filters\Filter::make('root_only')
                    ->label('Root Only')
                    ->query(fn (Builder $query): Builder => $query->whereNull('parent_id')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (CostCenter $record) {
                        // Check if has children
                        if ($record->children()->count() > 0) {
                            throw new \Exception('Cost center tidak dapat dihapus karena memiliki child cost centers');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->children()->count() > 0) {
                                    throw new \Exception("Cost center {$record->name} tidak dapat dihapus karena memiliki child cost centers");
                                }
                            }
                        }),
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
            'index' => Pages\ListCostCenters::route('/'),
            'create' => Pages\CreateCostCenter::route('/create'),
            'view' => Pages\ViewCostCenter::route('/{record}'),
            'edit' => Pages\EditCostCenter::route('/{record}/edit'),
        ];
    }
}
