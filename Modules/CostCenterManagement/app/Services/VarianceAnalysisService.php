<?php

namespace Modules\CostCenterManagement\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\CostCenterManagement\Models\CostCenterBudget;
use Modules\CostCenterManagement\Models\CostCenterTransaction;
use Modules\CostCenterManagement\Models\ServiceLine;

class VarianceAnalysisService
{
    /**
     * Calculate variance (actual - budget) untuk cost center dalam periode tertentu
     * 
     * @param int $costCenterId
     * @param Carbon $periodStart
     * @param Carbon $periodEnd
     * @return array ['category' => ['budget' => float, 'actual' => float, 'variance' => float, 'variance_percentage' => float]]
     */
    public function calculateVariance(int $costCenterId, Carbon $periodStart, Carbon $periodEnd): array
    {
        $costCenter = CostCenter::findOrFail($costCenterId);
        
        // Get budget data untuk periode
        $budgets = CostCenterBudget::where('cost_center_id', $costCenterId)
            ->where('fiscal_year', $periodStart->year)
            ->whereBetween('period_month', [$periodStart->month, $periodEnd->month])
            ->get()
            ->groupBy('category');
        
        // Get actual transactions untuk periode
        $transactions = CostCenterTransaction::where('cost_center_id', $costCenterId)
            ->whereBetween('transaction_date', [$periodStart, $periodEnd])
            ->where('transaction_type', '!=', 'revenue')
            ->get()
            ->groupBy('category');
        
        $variances = [];
        
        // Calculate variance per category
        $categories = ['personnel', 'supplies', 'services', 'depreciation', 'overhead', 'other'];
        
        foreach ($categories as $category) {
            $budgetAmount = $budgets->get($category)?->sum('budget_amount') ?? 0;
            $actualAmount = $transactions->get($category)?->sum('amount') ?? 0;
            $variance = $actualAmount - $budgetAmount;
            $variancePercentage = $budgetAmount > 0 ? ($variance / $budgetAmount) * 100 : 0;
            
            $variances[$category] = [
                'budget' => $budgetAmount,
                'actual' => $actualAmount,
                'variance' => $variance,
                'variance_percentage' => round($variancePercentage, 2),
                'classification' => $this->classifyVariance($variance, $budgetAmount)
            ];
        }
        
        // Calculate total
        $totalBudget = collect($variances)->sum('budget');
        $totalActual = collect($variances)->sum('actual');
        $totalVariance = $totalActual - $totalBudget;
        $totalVariancePercentage = $totalBudget > 0 ? ($totalVariance / $totalBudget) * 100 : 0;
        
        $variances['total'] = [
            'budget' => $totalBudget,
            'actual' => $totalActual,
            'variance' => $totalVariance,
            'variance_percentage' => round($totalVariancePercentage, 2),
            'classification' => $this->classifyVariance($totalVariance, $totalBudget)
        ];
        
        return $variances;
    }
    
    /**
     * Classify variance sebagai favorable atau unfavorable
     * 
     * @param float $variance
     * @param float $budget
     * @return string 'favorable' | 'unfavorable' | 'neutral'
     */
    public function classifyVariance(float $variance, float $budget): string
    {
        // Untuk biaya: variance negatif = favorable (actual < budget)
        // Untuk biaya: variance positif = unfavorable (actual > budget)
        
        if ($variance == 0) {
            return 'neutral';
        }
        
        // Threshold 5% untuk menentukan significance
        $threshold = abs($budget * 0.05);
        
        if (abs($variance) < $threshold) {
            return 'neutral';
        }
        
        return $variance < 0 ? 'favorable' : 'unfavorable';
    }
    
    /**
     * Get trend analysis untuk cost center dalam beberapa bulan terakhir
     * 
     * @param int $costCenterId
     * @param int $months Jumlah bulan untuk analisis
     * @return array [['period' => string, 'budget' => float, 'actual' => float, 'variance' => float]]
     */
    public function getTrendAnalysis(int $costCenterId, int $months = 12): array
    {
        $costCenter = CostCenter::findOrFail($costCenterId);
        $trends = [];
        
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subMonths($months - 1)->startOfMonth();
        
        for ($i = 0; $i < $months; $i++) {
            $periodStart = $startDate->copy()->addMonths($i)->startOfMonth();
            $periodEnd = $periodStart->copy()->endOfMonth();
            
            // Get budget untuk bulan ini
            $budget = CostCenterBudget::where('cost_center_id', $costCenterId)
                ->where('fiscal_year', $periodStart->year)
                ->where('period_month', $periodStart->month)
                ->sum('budget_amount');
            
            // Get actual untuk bulan ini
            $actual = CostCenterTransaction::where('cost_center_id', $costCenterId)
                ->whereBetween('transaction_date', [$periodStart, $periodEnd])
                ->where('transaction_type', '!=', 'revenue')
                ->sum('amount');
            
            $variance = $actual - $budget;
            
            $trends[] = [
                'period' => $periodStart->format('Y-m'),
                'period_label' => $periodStart->format('M Y'),
                'budget' => $budget,
                'actual' => $actual,
                'variance' => $variance,
                'variance_percentage' => $budget > 0 ? round(($variance / $budget) * 100, 2) : 0,
                'classification' => $this->classifyVariance($variance, $budget)
            ];
        }
        
        return $trends;
    }
    
    /**
     * Compare service lines berdasarkan biaya dan profitabilitas
     * 
     * @param array $serviceLineIds
     * @param Carbon $periodStart
     * @param Carbon $periodEnd
     * @return array
     */
    public function compareServiceLines(array $serviceLineIds, Carbon $periodStart, Carbon $periodEnd): array
    {
        $comparisons = [];
        
        foreach ($serviceLineIds as $serviceLineId) {
            $serviceLine = ServiceLine::with('members.costCenter')->findOrFail($serviceLineId);
            
            $totalCost = 0;
            $totalRevenue = 0;
            
            // Aggregate costs dari semua cost centers dalam service line
            foreach ($serviceLine->members as $member) {
                $costCenter = $member->costCenter;
                
                // Get costs
                $costs = CostCenterTransaction::where('cost_center_id', $costCenter->id)
                    ->whereBetween('transaction_date', [$periodStart, $periodEnd])
                    ->where('transaction_type', '!=', 'revenue')
                    ->sum('amount');
                
                // Apply allocation percentage untuk shared cost centers
                $allocatedCost = $costs * ($member->allocation_percentage / 100);
                $totalCost += $allocatedCost;
                
                // Get revenue jika profit center
                if ($costCenter->type === 'profit_center') {
                    $revenue = CostCenterTransaction::where('cost_center_id', $costCenter->id)
                        ->whereBetween('transaction_date', [$periodStart, $periodEnd])
                        ->where('transaction_type', 'revenue')
                        ->sum('amount');
                    
                    $allocatedRevenue = $revenue * ($member->allocation_percentage / 100);
                    $totalRevenue += $allocatedRevenue;
                }
            }
            
            $profit = $totalRevenue - $totalCost;
            $profitMargin = $totalRevenue > 0 ? ($profit / $totalRevenue) * 100 : 0;
            
            $comparisons[] = [
                'service_line_id' => $serviceLine->id,
                'service_line_name' => $serviceLine->name,
                'category' => $serviceLine->category,
                'total_cost' => $totalCost,
                'total_revenue' => $totalRevenue,
                'profit' => $profit,
                'profit_margin' => round($profitMargin, 2),
                'cost_centers_count' => $serviceLine->members->count()
            ];
        }
        
        // Sort by profit margin descending
        usort($comparisons, function($a, $b) {
            return $b['profit_margin'] <=> $a['profit_margin'];
        });
        
        return $comparisons;
    }
    
    /**
     * Generate comprehensive variance report untuk multiple cost centers
     * 
     * @param array $costCenterIds
     * @param Carbon $periodStart
     * @param Carbon $periodEnd
     * @return Collection
     */
    public function generateVarianceReport(array $costCenterIds, Carbon $periodStart, Carbon $periodEnd): Collection
    {
        $reports = collect();
        
        foreach ($costCenterIds as $costCenterId) {
            $costCenter = CostCenter::with('organizationUnit', 'parent')->findOrFail($costCenterId);
            
            $variances = $this->calculateVariance($costCenterId, $periodStart, $periodEnd);
            
            $reports->push([
                'cost_center_id' => $costCenter->id,
                'cost_center_code' => $costCenter->code,
                'cost_center_name' => $costCenter->name,
                'cost_center_type' => $costCenter->type,
                'organization_unit' => $costCenter->organizationUnit?->name,
                'parent_cost_center' => $costCenter->parent?->name,
                'period_start' => $periodStart->format('Y-m-d'),
                'period_end' => $periodEnd->format('Y-m-d'),
                'variances' => $variances,
                'summary' => [
                    'total_budget' => $variances['total']['budget'],
                    'total_actual' => $variances['total']['actual'],
                    'total_variance' => $variances['total']['variance'],
                    'variance_percentage' => $variances['total']['variance_percentage'],
                    'classification' => $variances['total']['classification']
                ]
            ]);
        }
        
        return $reports;
    }
}
