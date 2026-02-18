<?php

namespace Modules\CostCenterManagement\Filament\Resources;

use Modules\CostCenterManagement\Filament\Resources\CostCenterBudgetResource\Pages;
use Modules\CostCenterManagement\Models\CostCenterBudget;
use Modules\CostCenterManagement\Models\CostCenter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class CostCenterBudgetResource extends Resource
{
    protected static ?string $model = CostCenterBudget::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Budget Management';

    protected static ?string $navigationGroup = 'Cost Center Management';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Budget Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('cost_center_id')
                                    ->label('Cost Center')
                                    ->options(CostCenter::where('is_active', true)->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('category')
                                    ->label('Category')
                                    ->options([
                                        'personnel' => 'Personnel',
                                        'supplies' => 'Supplies',
                                        'services' => 'Services',
                                        'depreciation' => 'Depreciation',
                                        'overhead' => 'Overhead',
                                        'other' => 'Other',
                                    ])
                                    ->required(),

                                Forms\Components\TextInput::make('fiscal_year')
                                    ->label('Fiscal Year')
                                    ->numeric()
                                    ->required()
                                    ->default(now()->year)
                                    ->minValue(2020)
                                    ->maxValue(2050),

                                Forms\Components\Select::make('period_month')
                                    ->label('Period Month')
                                    ->options([
                                        1 => 'January',
                                        2 => 'February',
                                        3 => 'March',
                                        4 => 'April',
                                        5 => 'May',
                                        6 => 'June',
                                        7 => 'July',
                                        8 => 'August',
                                        9 => 'September',
                                        10 => 'October',
                                        11 => 'November',
                                        12 => 'December',
                                    ])
                                    ->required()
                                    ->default(now()->month),

                                Forms\Components\TextInput::make('budget_amount')
                                    ->label('Budget Amount')
                                    ->numeric()
                                    ->required()
                                    ->prefix('Rp')
                                    ->minValue(0)
                                    ->step(0.01),

                                Forms\Components\TextInput::make('actual_amount')
                                    ->label('Actual Amount')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->default(0),
                            ]),
                    ]),

                Section::make('Revision Information')
                    ->schema([
                        Forms\Components\TextInput::make('revision_number')
                            ->label('Revision Number')
                            ->numeric()
                            ->disabled()
                            ->default(0),

                        Forms\Components\Textarea::make('revision_justification')
                            ->label('Revision Justification')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('costCenter.code')
                    ->label('Cost Center Code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('costCenter.name')
                    ->label('Cost Center Name')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('fiscal_year')
                    ->label('Year')
                    ->sortable(),

                Tables\Columns\TextColumn::make('period_month')
                    ->label('Month')
                    ->formatStateUsing(fn ($state) => date('F', mktime(0, 0, 0, $state, 1)))
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->colors([
                        'primary' => 'personnel',
                        'success' => 'supplies',
                        'warning' => 'services',
                        'danger' => 'depreciation',
                        'info' => 'overhead',
                        'secondary' => 'other',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('budget_amount')
                    ->label('Budget')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('actual_amount')
                    ->label('Actual')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('variance_amount')
                    ->label('Variance')
                    ->money('IDR')
                    ->color(fn ($state) => $state > 0 ? 'danger' : ($state < 0 ? 'success' : 'secondary'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('utilization_percentage')
                    ->label('Utilization')
                    ->suffix('%')
                    ->color(fn ($state) => $state > 100 ? 'danger' : ($state > 80 ? 'warning' : 'success'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('revision_number')
                    ->label('Rev.')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_over_budget')
                    ->label('Over Budget')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->isOverBudget())
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),
            ])
            ->filters([
                SelectFilter::make('cost_center_id')
                    ->label('Cost Center')
                    ->options(CostCenter::pluck('name', 'id'))
                    ->searchable()
                    ->preload(),

                SelectFilter::make('fiscal_year')
                    ->label('Fiscal Year')
                    ->options(function () {
                        $years = [];
                        for ($i = now()->year - 2; $i <= now()->year + 1; $i++) {
                            $years[$i] = $i;
                        }
                        return $years;
                    }),

                SelectFilter::make('period_month')
                    ->label('Month')
                    ->options([
                        1 => 'January', 2 => 'February', 3 => 'March',
                        4 => 'April', 5 => 'May', 6 => 'June',
                        7 => 'July', 8 => 'August', 9 => 'September',
                        10 => 'October', 11 => 'November', 12 => 'December',
                    ]),

                SelectFilter::make('category')
                    ->options([
                        'personnel' => 'Personnel',
                        'supplies' => 'Supplies',
                        'services' => 'Services',
                        'depreciation' => 'Depreciation',
                        'overhead' => 'Overhead',
                        'other' => 'Other',
                    ]),

                Filter::make('over_budget')
                    ->label('Over Budget')
                    ->query(fn (Builder $query) => $query->whereRaw('actual_amount > budget_amount')),

                Filter::make('over_threshold')
                    ->label('Over Threshold (>80%)')
                    ->query(fn (Builder $query) => $query->where('utilization_percentage', '>', 80)),

                Filter::make('current_revision')
                    ->label('Current Revision Only')
                    ->query(fn (Builder $query) => $query->currentRevision())
                    ->default(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('revise')
                    ->label('Revise Budget')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('budget_amount')
                            ->label('New Budget Amount')
                            ->numeric()
                            ->required()
                            ->prefix('Rp')
                            ->minValue(0),
                        Forms\Components\Textarea::make('revision_justification')
                            ->label('Justification')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (CostCenterBudget $record, array $data) {
                        $service = app(\Modules\CostCenterManagement\Services\BudgetTrackingService::class);
                        $service->reviseBudget(
                            $record->id,
                            ['budget_amount' => $data['budget_amount']],
                            $data['revision_justification']
                        );
                    })
                    ->successNotificationTitle('Budget revised successfully'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('fiscal_year', 'desc');
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
            'index' => Pages\ListCostCenterBudgets::route('/'),
            'create' => Pages\CreateCostCenterBudget::route('/create'),
            'view' => Pages\ViewCostCenterBudget::route('/{record}'),
            'edit' => Pages\EditCostCenterBudget::route('/{record}/edit'),
        ];
    }
}
