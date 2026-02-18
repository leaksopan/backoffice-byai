<?php

namespace Modules\CostCenterManagement\Filament\Resources\CostCenterResource\Pages;

use Modules\CostCenterManagement\Filament\Resources\CostCenterResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCostCenter extends CreateRecord
{
    protected static string $resource = CostCenterResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['level'] = 0; // Will be calculated based on parent
        
        // Calculate level based on parent
        if (!empty($data['parent_id'])) {
            $parent = \Modules\CostCenterManagement\Models\CostCenter::find($data['parent_id']);
            if ($parent) {
                $data['level'] = $parent->level + 1;
            }
        }
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
