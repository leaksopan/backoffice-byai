<?php

namespace Modules\MasterDataManagement\Filament\Resources\MdmAssetResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterDataManagement\Filament\Resources\MdmAssetResource;

class ListMdmAssets extends ListRecords
{
    protected static string $resource = MdmAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
