<?php

namespace Modules\CostCenterManagement\Livewire;

use Livewire\Component;
use Carbon\Carbon;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\CostCenterManagement\Models\CostCenterTransaction;

class CostDistributionChart extends Component
{
    public int $costCenterId;
    public int $year;
    public int $month;
    public ?string $selectedCategory = null;
    public array $drillDownData = [];
    
    protected $listeners = ['periodChanged' => 'updatePeriod'];
    
    public function mount(int $costCenterId, int $year, int $month)
    {
        $this->costCenterId = $costCenterId;
        $this->year = $year;
        $this->month = $month;
    }
    
    public function updatePeriod(int $year, int $month)
    {
        $this->year = $year;
        $this->month = $month;
        $this->selectedCategory = null;
        $this->drillDownData = [];
    }
    
    public function selectCategory(string $category)
    {
        $this->selectedCategory = $category;
        $this->loadDrillDownData($category);
    }
    
    public function clearSelection()
    {
        $this->selectedCategory = null;
        $this->drillDownData = [];
    }
    
    protected function loadDrillDownData(string $category)
    {
        $periodStart = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $periodEnd = Carbon::create($this->year, $this->month, 1)->endOfMonth();
        
        $transactions = CostCenterTransaction::where('cost_center_id', $this->costCenterId)
            ->whereBetween('transaction_date', [$periodStart, $periodEnd])
            ->where('category', $category)
            ->where('transaction_type', '!=', 'revenue')
            ->orderBy('transaction_date', 'desc')
            ->get();
        
        $this->drillDownData = [
            'category' => $category,
            'total' => $transactions->sum('amount'),
            'count' => $transactions->count(),
            'transactions' => $transactions->map(function ($transaction) {
                return [
                    'date' => $transaction->transaction_date->format('d M Y'),
                    'type' => $transaction->transaction_type,
                    'amount' => $transaction->amount,
                    'description' => $transaction->description,
                    'reference_type' => $transaction->reference_type,
                ];
            })->toArray(),
        ];
    }
    
    public function getChartDataProperty()
    {
        $periodStart = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $periodEnd = Carbon::create($this->year, $this->month, 1)->endOfMonth();
        
        $distribution = CostCenterTransaction::where('cost_center_id', $this->costCenterId)
            ->whereBetween('transaction_date', [$periodStart, $periodEnd])
            ->where('transaction_type', '!=', 'revenue')
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();
        
        $totalCost = $distribution->sum('total');
        
        $chartData = [
            'labels' => [],
            'values' => [],
            'percentages' => [],
            'colors' => [
                'personnel' => 'rgba(59, 130, 246, 0.8)',
                'supplies' => 'rgba(16, 185, 129, 0.8)',
                'services' => 'rgba(245, 158, 11, 0.8)',
                'depreciation' => 'rgba(239, 68, 68, 0.8)',
                'overhead' => 'rgba(139, 92, 246, 0.8)',
                'other' => 'rgba(107, 114, 128, 0.8)',
            ],
        ];
        
        foreach ($distribution as $item) {
            $percentage = $totalCost > 0 ? ($item->total / $totalCost) * 100 : 0;
            
            $chartData['labels'][] = ucfirst($item->category);
            $chartData['values'][] = $item->total;
            $chartData['percentages'][] = round($percentage, 2);
        }
        
        return $chartData;
    }
    
    public function render()
    {
        return view('costcentermanagement::livewire.cost-distribution-chart');
    }
}
