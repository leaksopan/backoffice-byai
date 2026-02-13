<?php

namespace App\Filament\Resources\ModuleFormResource\Pages;

use App\Filament\Resources\ModuleFormResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListModuleForms extends ListRecords
{
    protected static string $resource = ModuleFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
