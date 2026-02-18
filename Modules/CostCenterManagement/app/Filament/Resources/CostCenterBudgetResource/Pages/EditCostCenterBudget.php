<?php

namespace Modules\CostCenterManagement\Filament\Resources\CostCenterBudgetResource\Pages;

use Modules\CostCenterManagement\Filament\Resources\CostCenterBudgetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCostCenterBudget extends EditRecord
{
    protected static string $resource = CostCenterBudgetResource::class;

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
