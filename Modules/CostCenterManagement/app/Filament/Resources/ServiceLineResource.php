<?php

namespace Modules\CostCenterManagement\Filament\Resources;

use Modules\CostCenterManagement\Filament\Resources\ServiceLineResource\Pages;
use Modules\CostCenterManagement\Models\ServiceLine;
use Modules\CostCenterManagement\Models\CostCenter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceLineResource extends Resource
{
    protected static ?string $model = ServiceLine::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Service Lines';

    protected static ?string $modelLabel = 'Service Line';

    protected static ?string $pluralModelLabel = 'Service Lines';

    protected static ?string $navigationGroup = 'Cost Center Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Kode Service Line')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->placeholder('SL-001')
                            ->helperText('Kode unik untuk service line'),

                        Forms\Components\TextInput::make('name')
                            ->label('Nama Service Line')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Layanan Rawat Jalan Umum'),

                        Forms\Components\Select::make('category')
                            ->label('Kategori')
                            ->required()
                            ->options([
                                'rawat_jalan' => 'Rawat Jalan',
                                'rawat_inap' => 'Rawat Inap',
                                'igd' => 'IGD',
                                'operasi' => 'Operasi',
                                'persalinan' => 'Persalinan',
                                'icu' => 'ICU',
                                'penunjang' => 'Penunjang',
                            ])
                            ->native(false)
                            ->helperText('Kategori service line'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Detail')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->helperText('Service line yang tidak aktif tidak akan digunakan dalam analisis'),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->maxLength(65535)
                            ->placeholder('Deskripsi service line...')
                            ->helperText('Penjelasan tentang service line ini')
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

                                Forms\Components\TextInput::make('allocation_percentage')
                                    ->label('Allocation %')
                                    ->required()
                                    ->numeric()
                                    ->default(100.00)
                                    ->minValue(0.01)
                                    ->maxValue(100.00)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Persentase alokasi untuk shared cost centers (100% = dedicated)')
                                    ->columnSpan(1),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Cost Center')
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                $state['cost_center_id'] 
                                    ? CostCenter::find($state['cost_center_id'])?->name . ' (' . ($state['allocation_percentage'] ?? 100) . '%)'
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

                Tables\Columns\BadgeColumn::make('category')
                    ->label('Kategori')
                    ->colors([
                        'primary' => 'rawat_jalan',
                        'success' => 'rawat_inap',
                        'danger' => 'igd',
                        'warning' => 'operasi',
                        'info' => 'persalinan',
                        'secondary' => 'icu',
                        'gray' => 'penunjang',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'rawat_jalan' => 'Rawat Jalan',
                        'rawat_inap' => 'Rawat Inap',
                        'igd' => 'IGD',
                        'operasi' => 'Operasi',
                        'persalinan' => 'Persalinan',
                        'icu' => 'ICU',
                        'penunjang' => 'Penunjang',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('members_count')
                    ->label('Jumlah Cost Centers')
                    ->counts('members')
                    ->sortable(),

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
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'rawat_jalan' => 'Rawat Jalan',
                        'rawat_inap' => 'Rawat Inap',
                        'igd' => 'IGD',
                        'operasi' => 'Operasi',
                        'persalinan' => 'Persalinan',
                        'icu' => 'ICU',
                        'penunjang' => 'Penunjang',
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
            'index' => Pages\ListServiceLines::route('/'),
            'create' => Pages\CreateServiceLine::route('/create'),
            'view' => Pages\ViewServiceLine::route('/{record}'),
            'edit' => Pages\EditServiceLine::route('/{record}/edit'),
        ];
    }
}
