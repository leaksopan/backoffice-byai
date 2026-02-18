<?php

namespace Modules\CostCenterManagement\Filament\Resources\CostCenterResource\Pages;

use Modules\CostCenterManagement\Filament\Resources\CostCenterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCostCenters extends ListRecords
{
    protected static string $resource = CostCenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('tree_view')
                ->label('Tree View')
                ->icon('heroicon-o-view-columns')
                ->url(route('ccm.cost-centers.tree'))
                ->color('gray'),
            Actions\CreateAction::make(),
        ];
    }
}
