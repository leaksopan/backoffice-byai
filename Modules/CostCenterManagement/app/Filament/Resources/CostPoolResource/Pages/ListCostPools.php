<?php

namespace Modules\CostCenterManagement\Filament\Resources\CostPoolResource\Pages;

use Modules\CostCenterManagement\Filament\Resources\CostPoolResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCostPools extends ListRecords
{
    protected static string $resource = CostPoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
