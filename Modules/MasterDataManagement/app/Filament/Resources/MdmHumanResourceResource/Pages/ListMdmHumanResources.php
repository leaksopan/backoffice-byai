<?php

namespace Modules\MasterDataManagement\Filament\Resources\MdmHumanResourceResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterDataManagement\Filament\Resources\MdmHumanResourceResource;

class ListMdmHumanResources extends ListRecords
{
    protected static string $resource = MdmHumanResourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
