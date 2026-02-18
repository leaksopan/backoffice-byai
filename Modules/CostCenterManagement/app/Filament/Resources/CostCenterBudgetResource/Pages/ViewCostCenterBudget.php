<?php

namespace Modules\CostCenterManagement\Filament\Resources\CostCenterBudgetResource\Pages;

use Modules\CostCenterManagement\Filament\Resources\CostCenterBudgetResource;
use Modules\CostCenterManagement\App\Models\CostCenterBudget;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;

class ViewCostCenterBudget extends ViewRecord
{
    protected static string $resource = CostCenterBudgetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Budget Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('costCenter.code')
                                    ->label('Cost Center Code'),
                                TextEntry::make('costCenter.name')
                                    ->label('Cost Center Name'),
                                TextEntry::make('fiscal_year')
                                    ->label('Fiscal Year'),
                                TextEntry::make('period_month')
                                    ->label('Period Month')
                                    ->formatStateUsing(fn ($state) => date('F', mktime(0, 0, 0, $state, 1))),
                                TextEntry::make('category')
                                    ->label('Category')
                                    ->badge(),
                                TextEntry::make('budget_amount')
                                    ->label('Budget Amount')
                                    ->money('IDR'),
                                TextEntry::make('actual_amount')
                                    ->label('Actual Amount')
                                    ->money('IDR'),
                                TextEntry::make('variance_amount')
                                    ->label('Variance')
                                    ->money('IDR')
                                    ->color(fn ($state) => $state > 0 ? 'danger' : ($state < 0 ? 'success' : 'secondary')),
                                TextEntry::make('utilization_percentage')
                                    ->label('Utilization')
                                    ->suffix('%')
                                    ->color(fn ($state) => $state > 100 ? 'danger' : ($state > 80 ? 'warning' : 'success')),
                                TextEntry::make('remaining_budget')
                                    ->label('Remaining Budget')
                                    ->money('IDR')
                                    ->getStateUsing(fn ($record) => $record->getRemainingBudget()),
                            ]),
                    ]),

                Section::make('Revision Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('revision_number')
                                    ->label('Revision Number'),
                                TextEntry::make('revision_justification')
                                    ->label('Revision Justification')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->visible(fn ($record) => $record->revision_number > 0),

                Section::make('Audit Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('createdBy.name')
                                    ->label('Created By'),
                                TextEntry::make('created_at')
                                    ->label('Created At')
                                    ->dateTime(),
                                TextEntry::make('updatedBy.name')
                                    ->label('Updated By'),
                                TextEntry::make('updated_at')
                                    ->label('Updated At')
                                    ->dateTime(),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
