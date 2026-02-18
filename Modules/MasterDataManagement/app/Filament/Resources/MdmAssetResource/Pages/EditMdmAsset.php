<?php

namespace Modules\MasterDataManagement\Filament\Resources\MdmAssetResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterDataManagement\Filament\Resources\MdmAssetResource;

class EditMdmAsset extends EditRecord
{
    protected static string $resource = MdmAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
