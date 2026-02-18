<?php

namespace Modules\MasterDataManagement\Filament\Resources\MdmAssetResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Modules\MasterDataManagement\Filament\Resources\MdmAssetResource;

class ViewMdmAsset extends ViewRecord
{
    protected static string $resource = MdmAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
