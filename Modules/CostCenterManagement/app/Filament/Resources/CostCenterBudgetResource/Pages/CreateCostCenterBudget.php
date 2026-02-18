<?php

namespace Modules\CostCenterManagement\Filament\Resources\CostCenterBudgetResource\Pages;

use Modules\CostCenterManagement\Filament\Resources\CostCenterBudgetResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCostCenterBudget extends CreateRecord
{
    protected static string $resource = CostCenterBudgetResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['revision_number'] = 0;
        $data['actual_amount'] = 0;
        $data['variance_amount'] = 0;
        $data['utilization_percentage'] = 0;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
