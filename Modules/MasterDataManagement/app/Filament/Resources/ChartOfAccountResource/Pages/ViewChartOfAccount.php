<?php

namespace Modules\MasterDataManagement\Filament\Resources\ChartOfAccountResource\Pages;

use Modules\MasterDataManagement\Filament\Resources\ChartOfAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewChartOfAccount extends ViewRecord
{
    protected static string $resource = ChartOfAccountResource::class;

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
                Infolists\Components\Section::make('Account Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('code')
                            ->label('Account Code')
                            ->copyable(),
                        
                        Infolists\Components\TextEntry::make('name')
                            ->label('Account Name'),
                        
                        Infolists\Components\TextEntry::make('category')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'asset' => 'success',
                                'liability' => 'danger',
                                'equity' => 'info',
                                'revenue' => 'warning',
                                'expense' => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                        
                        Infolists\Components\TextEntry::make('normal_balance')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'debit' => 'primary',
                                'credit' => 'success',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                        
                        Infolists\Components\TextEntry::make('parent.code')
                            ->label('Parent Account')
                            ->placeholder('—')
                            ->formatStateUsing(fn ($record) => 
                                $record->parent ? "{$record->parent->code} - {$record->parent->name}" : '—'
                            ),
                        
                        Infolists\Components\TextEntry::make('level')
                            ->label('Hierarchy Level'),
                        
                        Infolists\Components\IconEntry::make('is_header')
                            ->boolean()
                            ->label('Header Account'),
                        
                        Infolists\Components\IconEntry::make('is_active')
                            ->boolean()
                            ->label('Active Status'),
                        
                        Infolists\Components\TextEntry::make('external_code')
                            ->label('External Code')
                            ->placeholder('—'),
                    ])
                    ->columns(2),
                
                Infolists\Components\Section::make('Additional Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('description')
                            ->placeholder('No description')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
                
                Infolists\Components\Section::make('Metadata')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime(),
                        
                        Infolists\Components\TextEntry::make('updated_at')
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}
