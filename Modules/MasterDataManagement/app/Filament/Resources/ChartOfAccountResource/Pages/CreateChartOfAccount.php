<?php

namespace Modules\MasterDataManagement\Filament\Resources\ChartOfAccountResource\Pages;

use Modules\MasterDataManagement\Filament\Resources\ChartOfAccountResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChartOfAccount extends CreateRecord
{
    protected static string $resource = ChartOfAccountResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Calculate level based on parent
        if (!empty($data['parent_id'])) {
            $parent = static::getResource()::getModel()::find($data['parent_id']);
            $data['level'] = $parent ? $parent->level + 1 : 0;
        } else {
            $data['level'] = 0;
        }

        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
