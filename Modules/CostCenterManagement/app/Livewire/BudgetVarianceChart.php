<?php

namespace Modules\CostCenterManagement\Livewire;

use Livewire\Component;
use Carbon\Carbon;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\CostCenterManagement\Models\CostCenterTransaction;
use Modules\CostCenterManagement\Models\CostCenterBudget;

class BudgetVarianceChart extends Component
{
    public int $costCenterId;
    public int $year;
    public int $month;
    public ?string $selectedCategory = null;
    public array $categoryDetails = [];
    
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
        $this->categoryDetails = [];
    }
    
    public function selectCategory(string $category)
    {
        $this->selectedCategory = $category;
        $this->loadCategoryDetails($category);
    }
    
    public function clearSelection()
    {
        $this->selectedCategory = null;
        $this->categoryDetails = [];
    }
    
    protected function loadCategoryDetails(string $category)
    {
        $periodStart = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $periodEnd = Carbon::create($this->year, $this->month, 1)->endOfMonth();
        
        // Get budget
        $budget = CostCenterBudget::forCostCenter($this->costCenterId)
            ->forPeriod($this->year, $this->month)
            ->where('category', $category)
            ->currentRevision()
            ->first();
        
        // Get actual transactions
        $transactions = CostCenterTransaction::where('cost_center_id', $this->costCenterId)
            ->whereBetween('transaction_date', [$periodStart, $periodEnd])
            ->where('category', $category)
            ->where('transaction_type', '!=', 'revenue')
            ->orderBy('transaction_date', 'desc')
            ->get();
        
        $actualAmount = $transactions->sum('amount');
        $budgetAmount = $budget ? $budget->budget_amount : 0;
        $variance = $actualAmount - $budgetAmount;
        $variancePercentage = $budgetAmount > 0 ? ($variance / $budgetAmount) * 100 : 0;
        $utilization = $budgetAmount > 0 ? ($actualAmount / $budgetAmount) * 100 : 0;
        
        $this->categoryDetails = [
            'category' => $category,
            'budget' => $budgetAmount,
            'actual' => $actualAmount,
            'variance' => $variance,
            'variance_percentage' => $variancePercentage,
            'utilization' => $utilization,
            'remaining' => max(0, $budgetAmount - $actualAmount),
            'status' => $this->getUtilizationStatus($utilization),
            'classification' => $variance > 0 ? 'unfavorable' : 'favorable',
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
    
    protected function getUtilizationStatus(float $percentage): string
    {
        if ($percentage < 50) {
            return 'low';
        } elseif ($percentage < 80) {
            return 'normal';
        } elseif ($percentage < 100) {
            return 'warning';
        } else {
            return 'critical';
        }
    }
    
    public function getChartDataProperty()
    {
        $periodStart = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $periodEnd = Carbon::create($this->year, $this->month, 1)->endOfMonth();
        
        $categories = ['personnel', 'supplies', 'services', 'depreciation', 'overhead', 'other'];
        
        $chartData = [
            'labels' => [],
            'budget' => [],
            'actual' => [],
            'variance' => [],
        ];
        
        foreach ($categories as $category) {
            // Get budget
            $budget = CostCenterBudget::forCostCenter($this->costCenterId)
                ->forPeriod($this->year, $this->month)
                ->where('category', $category)
                ->currentRevision()
                ->first();
            
            // Get actual
            $actual = CostCenterTransaction::where('cost_center_id', $this->costCenterId)
                ->whereBetween('transaction_date', [$periodStart, $periodEnd])
                ->where('category', $category)
                ->where('transaction_type', '!=', 'revenue')
                ->sum('amount');
            
            $budgetAmount = $budget ? $budget->budget_amount : 0;
            $variance = $actual - $budgetAmount;
            
            $chartData['labels'][] = ucfirst($category);
            $chartData['budget'][] = $budgetAmount;
            $chartData['actual'][] = $actual;
            $chartData['variance'][] = $variance;
        }
        
        return $chartData;
    }
    
    public function render()
    {
        return view('costcentermanagement::livewire.budget-variance-chart');
    }
}
