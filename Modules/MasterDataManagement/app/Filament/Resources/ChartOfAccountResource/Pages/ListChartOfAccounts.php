<?php

namespace Modules\MasterDataManagement\Filament\Resources\ChartOfAccountResource\Pages;

use Modules\MasterDataManagement\Filament\Resources\ChartOfAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChartOfAccounts extends ListRecords
{
    protected static string $resource = ChartOfAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
