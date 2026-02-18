<?php

namespace Modules\CostCenterManagement\Livewire;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Livewire\Component;
use Modules\CostCenterManagement\Models\CostCenter;

class CostCenterTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(CostCenter::query()->with('parent'))
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state)))
                    ->colors([
                        'primary' => 'medical',
                        'success' => 'non_medical',
                        'warning' => 'administrative',
                        'info' => 'support',
                        'danger' => 'profit_center',
                    ]),
                TextColumn::make('parent.code')
                    ->label('Parent')
                    ->default('-')
                    ->sortable(),
                BadgeColumn::make('is_active')
                    ->label('Status')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ]),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'medical' => 'Medical',
                        'non_medical' => 'Non-Medical',
                        'administrative' => 'Administrative',
                        'support' => 'Support',
                        'profit_center' => 'Profit Center',
                    ]),
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
            ])
            ->actions([
                Action::make('edit')
                    ->label('Edit')
                    ->url(fn (CostCenter $record): string => route('ccm.cost-centers.edit', $record))
                    ->icon('heroicon-o-pencil')
                    ->visible(fn () => auth()->user()->can('cost-center-management.edit')),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->visible(fn () => auth()->user()->can('cost-center-management.delete'))
                    ->before(function (CostCenter $record) {
                        if ($record->children()->count() > 0) {
                            throw new \Exception('Tidak bisa hapus cost center yang masih punya child');
                        }
                    }),
            ])
            ->bulkActions([
                // Bulk actions if needed
            ])
            ->defaultSort('code');
    }

    public function render()
    {
        return view('costcentermanagement::livewire.cost-center-table');
    }
}
