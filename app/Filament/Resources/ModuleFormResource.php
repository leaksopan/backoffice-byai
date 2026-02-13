<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModuleFormResource\Pages;
use App\Models\ModuleForm;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ModuleFormResource extends Resource
{
    protected static ?string $model = ModuleForm::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('module_id')
                    ->relationship('module', 'name')
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('key')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('schema_json')
                    ->rows(12)
                    ->helperText('JSON schema for dynamic form.')
                    ->formatStateUsing(function ($state) {
                        if (is_array($state)) {
                            return json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                        }

                        return $state;
                    })
                    ->dehydrateStateUsing(function ($state) {
                        if (is_string($state)) {
                            return json_decode($state, true) ?? [];
                        }

                        return $state ?? [];
                    })
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('module.name')
                    ->label('Module')
                    ->sortable(),
                Tables\Columns\TextColumn::make('key')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModuleForms::route('/'),
            'create' => Pages\CreateModuleForm::route('/create'),
            'edit' => Pages\EditModuleForm::route('/{record}/edit'),
        ];
    }
}
