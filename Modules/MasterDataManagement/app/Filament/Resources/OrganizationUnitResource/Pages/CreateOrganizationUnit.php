<?php

namespace Modules\MasterDataManagement\Filament\Resources\OrganizationUnitResource\Pages;

use Modules\MasterDataManagement\Filament\Resources\OrganizationUnitResource;
use Modules\MasterDataManagement\Services\OrganizationHierarchyService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateOrganizationUnit extends CreateRecord
{
    protected static string $resource = OrganizationUnitResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set level based on parent
        if (isset($data['parent_id']) && $data['parent_id']) {
            $parent = static::getModel()::find($data['parent_id']);
            $data['level'] = $parent ? $parent->level + 1 : 0;
        } else {
            $data['level'] = 0;
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = parent::handleRecordCreation($data);

        // Update hierarchy path after creation
        $service = app(OrganizationHierarchyService::class);
        $service->updateHierarchyPath($record);

        return $record->fresh();
    }
}

