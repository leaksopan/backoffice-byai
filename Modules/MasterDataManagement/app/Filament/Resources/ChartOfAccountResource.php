<?php

namespace Modules\MasterDataManagement\Filament\Resources;

use Modules\MasterDataManagement\Filament\Resources\ChartOfAccountResource\Pages;
use Modules\MasterDataManagement\Models\MdmChartOfAccount;
use Modules\MasterDataManagement\Services\CoaValidationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class ChartOfAccountResource extends Resource
{
    protected static ?string $model = MdmChartOfAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Chart of Accounts';

    protected static ?string $modelLabel = 'Chart of Account';

    protected static ?string $pluralModelLabel = 'Chart of Accounts';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Account Information')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->label('Account Code')
                            ->placeholder('e.g., 1-01-01-01-001')
                            ->helperText('Format: X-XX-XX-XX-XXX')
                            ->rules([
                                fn () => function (string $attribute, $value, \Closure $fail) {
                                    $service = new CoaValidationService();
                                    if (!$service->validateCoaFormat($value)) {
                                        $fail('Format kode COA harus: X-XX-XX-XX-XXX (contoh: 1-01-01-01-001)');
                                    }
                                },
                            ]),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Account Name')
                            ->placeholder('e.g., Kas di Bank'),

                        Forms\Components\Select::make('category')
                            ->required()
                            ->options([
                                'asset' => 'Asset',
                                'liability' => 'Liability',
                                'equity' => 'Equity',
                                'revenue' => 'Revenue',
                                'expense' => 'Expense',
                            ])
                            ->label('Category')
                            ->native(false),

                        Forms\Components\Select::make('normal_balance')
                            ->required()
                            ->options([
                                'debit' => 'Debit',
                                'credit' => 'Credit',
                            ])
                            ->label('Normal Balance')
                            ->native(false)
                            ->helperText('Asset & Expense: Debit, Liability & Revenue & Equity: Credit'),

                        Forms\Components\Select::make('parent_id')
                            ->label('Parent Account')
                            ->options(function () {
                                return static::getCoaTreeOptions();
                            })
                            ->searchable()
                            ->nullable()
                            ->helperText('Select parent account to create hierarchy')
                            ->native(false),

                        Forms\Components\Toggle::make('is_header')
                            ->label('Header Account')
                            ->helperText('Header accounts cannot be used for transaction posting')
                            ->default(false)
                            ->inline(false),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false),

                        Forms\Components\TextInput::make('external_code')
                            ->maxLength(50)
                            ->label('External Code')
                            ->placeholder('e.g., SIMDA code')
                            ->helperText('For mapping with external systems (SIMDA, SIPD)'),
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
                    ->label('Code')
                    ->copyable()
                    ->copyMessage('Code copied'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Name')
                    ->description(fn (MdmChartOfAccount $record): string => 
                        $record->parent ? "Parent: {$record->parent->code} - {$record->parent->name}" : ''
                    )
                    ->wrap(),

                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'asset' => 'success',
                        'liability' => 'danger',
                        'equity' => 'info',
                        'revenue' => 'warning',
                        'expense' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('normal_balance')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'debit' => 'primary',
                        'credit' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('level')
                    ->label('Level')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_header')
                    ->boolean()
                    ->label('Header')
                    ->sortable()
                    ->tooltip(fn (MdmChartOfAccount $record): string => 
                        $record->is_header ? 'Cannot be used for posting' : 'Can be used for posting'
                    ),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->sortable(),

                Tables\Columns\TextColumn::make('external_code')
                    ->label('External Code')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('code', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'asset' => 'Asset',
                        'liability' => 'Liability',
                        'equity' => 'Equity',
                        'revenue' => 'Revenue',
                        'expense' => 'Expense',
                    ])
                    ->label('Category'),

                Tables\Filters\SelectFilter::make('normal_balance')
                    ->options([
                        'debit' => 'Debit',
                        'credit' => 'Credit',
                    ])
                    ->label('Normal Balance'),

                Tables\Filters\TernaryFilter::make('is_header')
                    ->label('Account Type')
                    ->placeholder('All accounts')
                    ->trueLabel('Header accounts only')
                    ->falseLabel('Postable accounts only'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All accounts')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (MdmChartOfAccount $record) {
                        $service = new CoaValidationService();
                        if (!$service->canDelete($record)) {
                            Notification::make()
                                ->danger()
                                ->title('Cannot delete account')
                                ->body('This account has child accounts or is used in transactions.')
                                ->send();
                            
                            throw new \Exception('Cannot delete account with children or transactions.');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            $service = new CoaValidationService();
                            foreach ($records as $record) {
                                if (!$service->canDelete($record)) {
                                    throw new \Exception("Cannot delete account '{$record->code}' with children or transactions.");
                                }
                            }
                        }),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('import')
                    ->label('Import CSV')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->url(route('mdm.coa.import'))
                    ->openUrlInNewTab(false),
                
                Tables\Actions\Action::make('export')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->url(route('mdm.coa.export'))
                    ->openUrlInNewTab(true),
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
            'index' => Pages\ListChartOfAccounts::route('/'),
            'create' => Pages\CreateChartOfAccount::route('/create'),
            'view' => Pages\ViewChartOfAccount::route('/{record}'),
            'edit' => Pages\EditChartOfAccount::route('/{record}/edit'),
        ];
    }

    /**
     * Get COA tree options for parent selector
     * Returns hierarchical list with indentation
     */
    protected static function getCoaTreeOptions(): array
    {
        $accounts = MdmChartOfAccount::query()
            ->orderBy('code')
            ->get();

        $options = [];
        foreach ($accounts as $account) {
            $indent = str_repeat('— ', $account->level);
            $options[$account->id] = $indent . $account->code . ' - ' . $account->name;
        }

        return $options;
    }

    /**
     * Get navigation badge (count of active accounts)
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
