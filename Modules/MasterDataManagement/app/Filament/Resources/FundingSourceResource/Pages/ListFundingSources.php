<?php

namespace Modules\MasterDataManagement\Filament\Resources\FundingSourceResource\Pages;

use Modules\MasterDataManagement\Filament\Resources\FundingSourceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFundingSources extends ListRecords
{
    protected static string $resource = FundingSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
