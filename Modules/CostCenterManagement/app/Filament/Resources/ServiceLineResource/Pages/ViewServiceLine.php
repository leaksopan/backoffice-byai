<?php

namespace Modules\CostCenterManagement\Filament\Resources\ServiceLineResource\Pages;

use Modules\CostCenterManagement\Filament\Resources\ServiceLineResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewServiceLine extends ViewRecord
{
    protected static string $resource = ServiceLineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
