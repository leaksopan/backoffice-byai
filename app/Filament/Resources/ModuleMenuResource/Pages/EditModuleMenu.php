<?php

namespace App\Filament\Resources\ModuleMenuResource\Pages;

use App\Filament\Resources\ModuleMenuResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditModuleMenu extends EditRecord
{
    protected static string $resource = ModuleMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
