<?php

namespace Modules\MasterDataManagement\Filament\Resources\OrganizationUnitResource\Pages;

use Modules\MasterDataManagement\Filament\Resources\OrganizationUnitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrganizationUnits extends ListRecords
{
    protected static string $resource = OrganizationUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

