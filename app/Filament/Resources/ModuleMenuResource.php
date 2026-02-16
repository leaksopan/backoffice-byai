<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModuleMenuResource\Pages;
use App\Models\ModuleMenu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ModuleMenuResource extends Resource
{
    protected static ?string $model = ModuleMenu::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('module_key')
                    ->relationship('module', 'name')
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('section')
                    ->options([
                        'MAIN' => 'MAIN',
                        'ADMIN' => 'ADMIN',
                    ])
                    ->default('MAIN')
                    ->required(),
                Forms\Components\TextInput::make('label')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('route_name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('url')
                    ->maxLength(255),
                Forms\Components\TextInput::make('icon')
                    ->maxLength(255),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('permission_name')
                    ->maxLength(255),
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
                Tables\Columns\TextColumn::make('label')
                    ->searchable(),
                Tables\Columns\TextColumn::make('route_name')
                    ->label('Route'),
                Tables\Columns\TextColumn::make('section')
                    ->label('Section'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),
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
            'index' => Pages\ListModuleMenus::route('/'),
            'create' => Pages\CreateModuleMenu::route('/create'),
            'edit' => Pages\EditModuleMenu::route('/{record}/edit'),
        ];
    }
}
