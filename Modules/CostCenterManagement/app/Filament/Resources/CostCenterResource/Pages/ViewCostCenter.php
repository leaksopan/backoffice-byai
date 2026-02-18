<?php

namespace Modules\CostCenterManagement\Filament\Resources\CostCenterResource\Pages;

use Modules\CostCenterManagement\Filament\Resources\CostCenterResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewCostCenter extends ViewRecord
{
    protected static string $resource = CostCenterResource::class;

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
                Infolists\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Infolists\Components\TextEntry::make('code')
                            ->label('Kode'),
                        Infolists\Components\TextEntry::make('name')
                            ->label('Nama'),
                        Infolists\Components\TextEntry::make('type')
                            ->label('Tipe')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'medical' => 'success',
                                'non_medical' => 'info',
                                'administrative' => 'warning',
                                'profit_center' => 'primary',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'medical' => 'Medical',
                                'non_medical' => 'Non-Medical',
                                'administrative' => 'Administrative',
                                'profit_center' => 'Profit Center',
                                default => $state,
                            }),
                        Infolists\Components\TextEntry::make('classification')
                            ->label('Klasifikasi')
                            ->default('-'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Struktur Organisasi')
                    ->schema([
                        Infolists\Components\TextEntry::make('organizationUnit.name')
                            ->label('Unit Organisasi'),
                        Infolists\Components\TextEntry::make('parent.name')
                            ->label('Parent Cost Center')
                            ->default('-'),
                        Infolists\Components\TextEntry::make('level')
                            ->label('Level Hierarki'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Manajemen & Status')
                    ->schema([
                        Infolists\Components\TextEntry::make('manager.name')
                            ->label('Manager')
                            ->default('-'),
                        Infolists\Components\TextEntry::make('effective_date')
                            ->label('Tanggal Efektif')
                            ->date('d M Y'),
                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Status Aktif')
                            ->boolean(),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Deskripsi')
                    ->schema([
                        Infolists\Components\TextEntry::make('description')
                            ->label('Deskripsi')
                            ->default('-')
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Informasi Sistem')
                    ->schema([
                        Infolists\Components\TextEntry::make('createdBy.name')
                            ->label('Dibuat Oleh')
                            ->default('-'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime('d M Y H:i'),
                        Infolists\Components\TextEntry::make('updatedBy.name')
                            ->label('Diubah Oleh')
                            ->default('-'),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Diubah Pada')
                            ->dateTime('d M Y H:i'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
