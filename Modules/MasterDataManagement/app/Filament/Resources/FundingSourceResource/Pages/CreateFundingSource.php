<?php

namespace Modules\MasterDataManagement\Filament\Resources\FundingSourceResource\Pages;

use Modules\MasterDataManagement\Filament\Resources\FundingSourceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFundingSource extends CreateRecord
{
    protected static string $resource = FundingSourceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
