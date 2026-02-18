<?php

namespace Modules\CostCenterManagement\Filament\Resources\CostCenterResource\Pages;

use Modules\CostCenterManagement\Filament\Resources\CostCenterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditCostCenter extends EditRecord
{
    protected static string $resource = CostCenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->before(function () {
                    if ($this->record->children()->count() > 0) {
                        throw new \Exception('Cost center tidak dapat dihapus karena memiliki child cost centers');
                    }
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = Auth::id();
        
        // Recalculate level based on parent
        if (!empty($data['parent_id'])) {
            $parent = \Modules\CostCenterManagement\Models\CostCenter::find($data['parent_id']);
            if ($parent) {
                $data['level'] = $parent->level + 1;
            }
        } else {
            $data['level'] = 0;
        }
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
