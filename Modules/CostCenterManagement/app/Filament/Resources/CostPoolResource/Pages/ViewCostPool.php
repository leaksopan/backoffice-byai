<?php

namespace Modules\CostCenterManagement\Filament\Resources\CostPoolResource\Pages;

use Modules\CostCenterManagement\Filament\Resources\CostPoolResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCostPool extends ViewRecord
{
    protected static string $resource = CostPoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
