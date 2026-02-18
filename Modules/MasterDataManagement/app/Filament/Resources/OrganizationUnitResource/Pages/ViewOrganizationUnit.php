<?php

namespace Modules\MasterDataManagement\Filament\Resources\OrganizationUnitResource\Pages;

use Modules\MasterDataManagement\Filament\Resources\OrganizationUnitResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewOrganizationUnit extends ViewRecord
{
    protected static string $resource = OrganizationUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Basic Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('code')
                            ->label('Unit Code'),
                        
                        Infolists\Components\TextEntry::make('name')
                            ->label('Unit Name'),
                        
                        Infolists\Components\TextEntry::make('type')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'installation' => 'success',
                                'department' => 'info',
                                'unit' => 'warning',
                                'section' => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => ucfirst($state))
                            ->label('Unit Type'),
                        
                        Infolists\Components\TextEntry::make('parent.name')
                            ->label('Parent Unit')
                            ->placeholder('—'),
                        
                        Infolists\Components\IconEntry::make('is_active')
                            ->boolean()
                            ->label('Active Status'),
                        
                        Infolists\Components\TextEntry::make('level')
                            ->label('Hierarchy Level'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Hierarchy Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('hierarchy_path')
                            ->label('Hierarchy Path')
                            ->placeholder('—'),
                        
                        Infolists\Components\TextEntry::make('children_count')
                            ->label('Number of Child Units')
                            ->state(fn ($record) => $record->children()->count()),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Additional Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('description')
                            ->label('Description')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('Timestamps')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime()
                            ->label('Created At'),
                        
                        Infolists\Components\TextEntry::make('updated_at')
                            ->dateTime()
                            ->label('Updated At'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}

