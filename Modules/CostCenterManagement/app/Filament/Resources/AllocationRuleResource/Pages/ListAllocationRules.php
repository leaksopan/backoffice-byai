<?php

namespace Modules\CostCenterManagement\Filament\Resources\AllocationRuleResource\Pages;

use Modules\CostCenterManagement\Filament\Resources\AllocationRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAllocationRules extends ListRecords
{
    protected static string $resource = AllocationRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
