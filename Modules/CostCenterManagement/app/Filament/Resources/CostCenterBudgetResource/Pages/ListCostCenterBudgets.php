<?php

namespace Modules\CostCenterManagement\Filament\Resources\CostCenterBudgetResource\Pages;

use Modules\CostCenterManagement\Filament\Resources\CostCenterBudgetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCostCenterBudgets extends ListRecords
{
    protected static string $resource = CostCenterBudgetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Budgets'),
            
            'current_month' => Tab::make('Current Month')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('fiscal_year', now()->year)
                    ->where('period_month', now()->month)
                    ->currentRevision()
                ),
            
            'over_budget' => Tab::make('Over Budget')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereRaw('actual_amount > budget_amount')
                    ->currentRevision()
                )
                ->badge(fn () => \Modules\CostCenterManagement\App\Models\CostCenterBudget::whereRaw('actual_amount > budget_amount')->currentRevision()->count()),
            
            'over_threshold' => Tab::make('Over Threshold')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('utilization_percentage', '>', 80)
                    ->currentRevision()
                )
                ->badge(fn () => \Modules\CostCenterManagement\App\Models\CostCenterBudget::where('utilization_percentage', '>', 80)->currentRevision()->count()),
        ];
    }
}
