<?php

namespace Modules\MasterDataManagement\Filament\Resources\ServiceCatalogResource\Pages;

use Modules\MasterDataManagement\Filament\Resources\ServiceCatalogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServiceCatalogs extends ListRecords
{
    protected static string $resource = ServiceCatalogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
