<?php

namespace Modules\CostCenterManagement\Livewire;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;
use Modules\CostCenterManagement\Models\CostCenter;

class CostCenterForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?CostCenter $costCenter = null;
    public ?array $data = [];

    public function mount(?CostCenter $costCenter = null): void
    {
        $this->costCenter = $costCenter;
        
        if ($costCenter) {
            $this->form->fill($costCenter->toArray());
        } else {
            $this->form->fill(['is_active' => true]);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('code')
                    ->label('Code')
                    ->required()
                    ->maxLength(50)
                    ->unique(CostCenter::class, 'code', ignoreRecord: $this->costCenter)
                    ->columnSpan(1),
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),
                Select::make('type')
                    ->label('Type')
                    ->required()
                    ->options([
                        'medical' => 'Medical',
                        'non_medical' => 'Non-Medical',
                        'administrative' => 'Administrative',
                        'support' => 'Support',
                        'profit_center' => 'Profit Center',
                    ])
                    ->columnSpan(1),
                Select::make('parent_id')
                    ->label('Parent Cost Center')
                    ->options(function () {
                        $query = CostCenter::query()
                            ->whereNull('parent_id')
                            ->orWhere('type', '!=', 'profit_center')
                            ->orderBy('code');

                        if ($this->costCenter) {
                            $query->where('id', '!=', $this->costCenter->id);
                        }

                        return $query->pluck('name', 'id');
                    })
                    ->searchable()
                    ->placeholder('None (Root Level)')
                    ->columnSpan(1),
                Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->columnSpanFull(),
                Checkbox::make('is_active')
                    ->label('Active')
                    ->default(true),
            ])
            ->columns(2)
            ->statePath('data');
    }

    public function save()
    {
        $data = $this->form->getState();

        if ($this->costCenter) {
            $this->costCenter->update($data);
            $message = 'Cost center berhasil diupdate';
        } else {
            CostCenter::create($data);
            $message = 'Cost center berhasil dibuat';
        }

        session()->flash('success', $message);
        return redirect()->route('ccm.cost-centers.index');
    }

    public function render()
    {
        return view('costcentermanagement::livewire.cost-center-form');
    }
}
