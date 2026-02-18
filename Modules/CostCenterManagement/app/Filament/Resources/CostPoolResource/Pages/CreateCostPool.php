<?php

namespace Modules\CostCenterManagement\Filament\Resources\CostPoolResource\Pages;

use Modules\CostCenterManagement\Filament\Resources\CostPoolResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCostPool extends CreateRecord
{
    protected static string $resource = CostPoolResource::class;

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
