<?php

namespace Modules\CostCenterManagement\Filament\Resources;

use Modules\CostCenterManagement\Filament\Resources\AllocationRuleResource\Pages;
use Modules\CostCenterManagement\Models\AllocationRule;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\CostCenterManagement\Events\AllocationRuleApprovalRequested;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AllocationRuleResource extends Resource
{
    protected static ?string $model = AllocationRule::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'Allocation Rules';

    protected static ?string $modelLabel = 'Allocation Rule';

    protected static ?string $pluralModelLabel = 'Allocation Rules';

    protected static ?string $navigationGroup = 'Cost Center Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Kode Allocation Rule')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->placeholder('AR-001')
                            ->helperText('Kode unik untuk allocation rule'),

                        Forms\Components\TextInput::make('name')
                            ->label('Nama Allocation Rule')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Alokasi Biaya Listrik'),

                        Forms\Components\Select::make('source_cost_center_id')
                            ->label('Source Cost Center')
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
                            ->helperText('Cost center sumber yang biayanya akan dialokasikan')
                            ->reactive(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Metode Alokasi')
                    ->schema([
                        Forms\Components\Select::make('allocation_base')
                            ->label('Dasar Alokasi')
                            ->required()
                            ->options([
                                'direct' => 'Direct (Langsung)',
                                'percentage' => 'Percentage (Persentase)',
                                'square_footage' => 'Square Footage (Luas Ruangan)',
                                'headcount' => 'Headcount (Jumlah Pegawai)',
                                'patient_days' => 'Patient Days (Hari Rawat)',
                                'service_volume' => 'Service Volume (Volume Layanan)',
                                'revenue' => 'Revenue (Pendapatan)',
                                'formula' => 'Formula (Custom)',
                            ])
                            ->native(false)
                            ->reactive()
                            ->helperText('Pilih metode yang akan digunakan untuk alokasi'),

                        Forms\Components\Textarea::make('allocation_formula')
                            ->label('Formula Alokasi')
                            ->rows(3)
                            ->maxLength(65535)
                            ->placeholder('source_amount * 0.5')
                            ->helperText('Formula custom untuk alokasi (hanya untuk allocation_base = formula)')
                            ->visible(fn (Forms\Get $get) => $get('allocation_base') === 'formula')
                            ->required(fn (Forms\Get $get) => $get('allocation_base') === 'formula'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Target Cost Centers')
                    ->schema([
                        Forms\Components\Repeater::make('targets')
                            ->label('Target Cost Centers')
                            ->relationship('targets')
                            ->schema([
                                Forms\Components\Select::make('target_cost_center_id')
                                    ->label('Target Cost Center')
                                    ->required()
                                    ->options(function (Forms\Get $get) {
                                        $sourceCostCenterId = $get('../../source_cost_center_id');
                                        
                                        return CostCenter::active()
                                            ->when($sourceCostCenterId, fn ($q) => $q->where('id', '!=', $sourceCostCenterId))
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
                                    ->label('Persentase (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->visible(fn (Forms\Get $get) => $get('../../allocation_base') === 'percentage')
                                    ->required(fn (Forms\Get $get) => $get('../../allocation_base') === 'percentage'),

                                Forms\Components\TextInput::make('allocation_weight')
                                    ->label('Weight')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->visible(fn (Forms\Get $get) => in_array($get('../../allocation_base'), ['square_footage', 'headcount', 'patient_days', 'service_volume', 'revenue']))
                                    ->required(fn (Forms\Get $get) => in_array($get('../../allocation_base'), ['square_footage', 'headcount', 'patient_days', 'service_volume', 'revenue'])),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Target')
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                $state['target_cost_center_id'] 
                                    ? CostCenter::find($state['target_cost_center_id'])?->name 
                                    : null
                            ),
                    ]),

                Forms\Components\Section::make('Status & Approval')
                    ->schema([
                        Forms\Components\DatePicker::make('effective_date')
                            ->label('Tanggal Efektif')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->helperText('Tanggal mulai berlaku allocation rule'),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Berakhir')
                            ->native(false)
                            ->helperText('Tanggal berakhir (opsional)'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->helperText('Allocation rule yang tidak aktif tidak akan dijalankan'),

                        Forms\Components\Select::make('approval_status')
                            ->label('Status Approval')
                            ->options([
                                'draft' => 'Draft',
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->default('draft')
                            ->native(false)
                            ->disabled(fn (?AllocationRule $record) => $record && $record->approval_status === 'approved')
                            ->helperText('Status approval allocation rule'),

                        Forms\Components\Textarea::make('justification')
                            ->label('Justifikasi')
                            ->rows(3)
                            ->maxLength(65535)
                            ->placeholder('Alasan pembuatan atau perubahan allocation rule...')
                            ->helperText('Jelaskan alasan pembuatan atau perubahan allocation rule')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
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

                Tables\Columns\TextColumn::make('sourceCostCenter.name')
                    ->label('Source Cost Center')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\BadgeColumn::make('allocation_base')
                    ->label('Dasar Alokasi')
                    ->colors([
                        'success' => 'percentage',
                        'info' => 'direct',
                        'warning' => fn ($state) => in_array($state, ['square_footage', 'headcount', 'patient_days', 'service_volume', 'revenue']),
                        'danger' => 'formula',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'direct' => 'Direct',
                        'percentage' => 'Percentage',
                        'square_footage' => 'Square Footage',
                        'headcount' => 'Headcount',
                        'patient_days' => 'Patient Days',
                        'service_volume' => 'Service Volume',
                        'revenue' => 'Revenue',
                        'formula' => 'Formula',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('targets_count')
                    ->label('Jumlah Target')
                    ->counts('targets')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('approval_status')
                    ->label('Status Approval')
                    ->colors([
                        'secondary' => 'draft',
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        default => $state,
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('effective_date')
                    ->label('Tanggal Efektif')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('approvedBy.name')
                    ->label('Approved By')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Approved At')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('allocation_base')
                    ->label('Dasar Alokasi')
                    ->options([
                        'direct' => 'Direct',
                        'percentage' => 'Percentage',
                        'square_footage' => 'Square Footage',
                        'headcount' => 'Headcount',
                        'patient_days' => 'Patient Days',
                        'service_volume' => 'Service Volume',
                        'revenue' => 'Revenue',
                        'formula' => 'Formula',
                    ])
                    ->native(false),

                Tables\Filters\SelectFilter::make('approval_status')
                    ->label('Status Approval')
                    ->options([
                        'draft' => 'Draft',
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->native(false),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif')
                    ->native(false),

                Tables\Filters\SelectFilter::make('source_cost_center_id')
                    ->label('Source Cost Center')
                    ->options(function () {
                        return CostCenter::active()
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn ($cc) => [$cc->id => "{$cc->code} - {$cc->name}"]);
                    })
                    ->searchable()
                    ->preload()
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\Action::make('request_approval')
                    ->label('Request Approval')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (AllocationRule $record) => $record->approval_status === 'draft')
                    ->action(function (AllocationRule $record) {
                        $record->update([
                            'approval_status' => 'pending',
                        ]);
                        
                        // Dispatch event untuk notifikasi
                        event(new AllocationRuleApprovalRequested($record, auth()->id()));
                    }),

                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (AllocationRule $record) => $record->approval_status === 'pending')
                    ->action(function (AllocationRule $record) {
                        $record->update([
                            'approval_status' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (AllocationRule $record) => $record->approval_status === 'pending')
                    ->action(function (AllocationRule $record) {
                        $record->update([
                            'approval_status' => 'rejected',
                        ]);
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->disabled(fn (AllocationRule $record) => $record->approval_status === 'approved'),
                Tables\Actions\DeleteAction::make()
                    ->disabled(fn (AllocationRule $record) => $record->approval_status === 'approved'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->approval_status === 'approved') {
                                    throw new \Exception("Allocation rule {$record->name} sudah approved dan tidak dapat dihapus");
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
            'index' => Pages\ListAllocationRules::route('/'),
            'create' => Pages\CreateAllocationRule::route('/create'),
            'view' => Pages\ViewAllocationRule::route('/{record}'),
            'edit' => Pages\EditAllocationRule::route('/{record}/edit'),
        ];
    }
}
