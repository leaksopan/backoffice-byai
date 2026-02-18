<?php

namespace Modules\MasterDataManagement\Filament\Resources\ServiceCatalogResource\Pages;

use Modules\MasterDataManagement\Filament\Resources\ServiceCatalogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceCatalog extends CreateRecord
{
    protected static string $resource = ServiceCatalogResource::class;

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
