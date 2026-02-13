<?php

namespace App\Filament\Resources\ModuleFormResource\Pages;

use App\Filament\Resources\ModuleFormResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditModuleForm extends EditRecord
{
    protected static string $resource = ModuleFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
