<?php

namespace Modules\CostCenterManagement\Filament\Resources\CostPoolResource\Pages;

use Modules\CostCenterManagement\Filament\Resources\CostPoolResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCostPool extends EditRecord
{
    protected static string $resource = CostPoolResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();
        
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
