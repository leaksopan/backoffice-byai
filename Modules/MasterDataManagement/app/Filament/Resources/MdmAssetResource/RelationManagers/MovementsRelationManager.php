<?php

namespace Modules\MasterDataManagement\Filament\Resources\MdmAssetResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;

class MovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'movements';

    protected static ?string $title = 'Riwayat Perpindahan';

    protected static ?string $recordTitleAttribute = 'movement_date';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('to_location_id')
                    ->label('Lokasi Tujuan')
                    ->required()
                    ->options(MdmOrganizationUnit::where('is_active', true)->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                
                Forms\Components\DatePicker::make('movement_date')
                    ->label('Tanggal Perpindahan')
                    ->required()
                    ->default(now()),
                
                Forms\Components\Textarea::make('reason')
                    ->label('Alasan')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('movement_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('fromLocation.name')
                    ->label('Dari Lokasi')
                    ->default('-'),
                
                Tables\Columns\TextColumn::make('toLocation.name')
                    ->label('Ke Lokasi'),
                
                Tables\Columns\TextColumn::make('reason')
                    ->label('Alasan')
                    ->limit(50),
            ])
            ->defaultSort('movement_date', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['from_location_id'] = $this->ownerRecord->current_location_id;
                        $data['created_by'] = auth()->id();
                        return $data;
                    })
                    ->after(function ($record) {
                        // Update asset's current location
                        $this->ownerRecord->update([
                            'current_location_id' => $record->to_location_id,
                        ]);
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }
}
