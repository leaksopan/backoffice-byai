<?php

namespace Modules\CostCenterManagement\Livewire;

use Livewire\Component;
use Carbon\Carbon;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\CostCenterManagement\Services\VarianceAnalysisService;

class TrendLineChart extends Component
{
    public int $costCenterId;
    public int $months = 12;
    public string $viewMode = 'both'; // 'both', 'budget', 'actual', 'variance'
    public ?string $selectedPeriod = null;
    public array $periodDetails = [];
    
    protected VarianceAnalysisService $varianceService;
    
    protected $listeners = ['periodChanged' => 'refresh'];
    
    public function boot(VarianceAnalysisService $varianceService)
    {
        $this->varianceService = $varianceService;
    }
    
    public function mount(int $costCenterId, int $months = 12)
    {
        $this->costCenterId = $costCenterId;
        $this->months = $months;
    }
    
    public function setMonths(int $months)
    {
        $this->months = $months;
        $this->selectedPeriod = null;
        $this->periodDetails = [];
    }
    
    public function setViewMode(string $mode)
    {
        $this->viewMode = $mode;
    }
    
    public function selectPeriod(string $period)
    {
        $this->selectedPeriod = $period;
        $this->loadPeriodDetails($period);
    }
    
    public function clearSelection()
    {
        $this->selectedPeriod = null;
        $this->periodDetails = [];
    }
    
    protected function loadPeriodDetails(string $period)
    {
        // Parse period string (format: "Jan 2026")
        $date = Carbon::createFromFormat('M Y', $period);
        $periodStart = $date->copy()->startOfMonth();
        $periodEnd = $date->copy()->endOfMonth();
        
        $variances = $this->varianceService->calculateVariance(
            $this->costCenterId,
            $periodStart,
            $periodEnd
        );
        
        $this->periodDetails = [
            'period' => $period,
            'period_start' => $periodStart->format('Y-m-d'),
            'period_end' => $periodEnd->format('Y-m-d'),
            'variances' => $variances,
        ];
    }
    
    public function getTrendDataProperty()
    {
        $trends = $this->varianceService->getTrendAnalysis($this->costCenterId, $this->months);
        
        return [
            'labels' => array_column($trends, 'period_label'),
            'budget' => array_column($trends, 'budget'),
            'actual' => array_column($trends, 'actual'),
            'variance' => array_column($trends, 'variance'),
            'variance_percentage' => array_column($trends, 'variance_percentage'),
        ];
    }
    
    public function render()
    {
        return view('costcentermanagement::livewire.trend-line-chart');
    }
}
