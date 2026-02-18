<?php

namespace Modules\MasterDataManagement\Filament\Resources\FundingSourceResource\Pages;

use Modules\MasterDataManagement\Filament\Resources\FundingSourceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFundingSource extends EditRecord
{
    protected static string $resource = FundingSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
