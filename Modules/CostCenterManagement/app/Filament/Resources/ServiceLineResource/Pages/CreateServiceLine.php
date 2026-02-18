<?php

namespace Modules\CostCenterManagement\Filament\Resources\ServiceLineResource\Pages;

use Modules\CostCenterManagement\Filament\Resources\ServiceLineResource;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceLine extends CreateRecord
{
    protected static string $resource = ServiceLineResource::class;

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
