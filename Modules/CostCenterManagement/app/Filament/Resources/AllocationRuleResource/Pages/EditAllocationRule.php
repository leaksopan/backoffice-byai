<?php

namespace Modules\CostCenterManagement\Filament\Resources\AllocationRuleResource\Pages;

use Modules\CostCenterManagement\Filament\Resources\AllocationRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAllocationRule extends EditRecord
{
    protected static string $resource = AllocationRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->disabled(fn () => $this->record->approval_status === 'approved'),
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
