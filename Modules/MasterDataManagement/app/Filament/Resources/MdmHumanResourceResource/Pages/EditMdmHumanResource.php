<?php

namespace Modules\MasterDataManagement\Filament\Resources\MdmHumanResourceResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterDataManagement\Filament\Resources\MdmHumanResourceResource;

class EditMdmHumanResource extends EditRecord
{
    protected static string $resource = MdmHumanResourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
