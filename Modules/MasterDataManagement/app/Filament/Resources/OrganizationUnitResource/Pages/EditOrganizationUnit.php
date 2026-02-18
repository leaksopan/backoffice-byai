<?php

namespace Modules\MasterDataManagement\Filament\Resources\OrganizationUnitResource\Pages;

use Modules\MasterDataManagement\Filament\Resources\OrganizationUnitResource;
use Modules\MasterDataManagement\Services\OrganizationHierarchyService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditOrganizationUnit extends EditRecord
{
    protected static string $resource = OrganizationUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->before(function () {
                    if ($this->record->children()->exists()) {
                        throw new \Exception('Cannot delete unit with child units. Please remove or reassign child units first.');
                    }
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Prevent selecting self or descendants as parent
        $service = app(OrganizationHierarchyService::class);
        
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Validate no circular reference
        if (isset($data['parent_id']) && $data['parent_id']) {
            $service = app(OrganizationHierarchyService::class);
            
            if (!$service->validateNoCircularReference($this->record->id, $data['parent_id'])) {
                throw new \Exception('Cannot set parent: This would create a circular reference in the hierarchy.');
            }

            // Update level based on new parent
            $parent = static::getModel()::find($data['parent_id']);
            $data['level'] = $parent ? $parent->level + 1 : 0;
        } else {
            $data['level'] = 0;
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $oldParentId = $record->parent_id;
        
        $record = parent::handleRecordUpdate($record, $data);

        // Update hierarchy path if parent changed
        if ($oldParentId !== $record->parent_id) {
            $service = app(OrganizationHierarchyService::class);
            $service->updateHierarchyPath($record);

            // Update all descendants
            $descendants = $service->getDescendants($record->id);
            foreach ($descendants as $descendant) {
                $service->updateHierarchyPath($descendant);
            }
        }

        return $record->fresh();
    }
}

