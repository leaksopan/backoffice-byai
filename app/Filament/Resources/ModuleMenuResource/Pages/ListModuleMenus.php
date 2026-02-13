<?php

namespace App\Filament\Resources\ModuleMenuResource\Pages;

use App\Filament\Resources\ModuleMenuResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListModuleMenus extends ListRecords
{
    protected static string $resource = ModuleMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
