<?php

namespace Modules\CostCenterManagement\Filament\Resources\AllocationRuleResource\Pages;

use Modules\CostCenterManagement\Filament\Resources\AllocationRuleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAllocationRule extends CreateRecord
{
    protected static string $resource = AllocationRuleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
