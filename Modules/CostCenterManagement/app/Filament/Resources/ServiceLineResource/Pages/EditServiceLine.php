<?php

namespace Modules\CostCenterManagement\Filament\Resources\ServiceLineResource\Pages;

use Modules\CostCenterManagement\Filament\Resources\ServiceLineResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditServiceLine extends EditRecord
{
    protected static string $resource = ServiceLineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
