<?php

namespace Modules\CostCenterManagement\Filament\Resources\AllocationRuleResource\Pages;

use Modules\CostCenterManagement\Filament\Resources\AllocationRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAllocationRule extends ViewRecord
{
    protected static string $resource = AllocationRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->disabled(fn () => $this->record->approval_status === 'approved'),
        ];
    }
}
