<?php

namespace Modules\CostCenterManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\CostCenterManagement\Models\ServiceLine;
use Modules\CostCenterManagement\Models\CostCenterTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ServiceLineController extends Controller
{
    /**
     * Display cost analysis for a service line.
     */
    public function costAnalysis(ServiceLine $serviceLine, Request $request)
    {
        $request->validate([
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after_or_equal:period_start',
        ]);

        $periodStart = $request->input('period_start', Carbon::now()->startOfMonth());
        $periodEnd = $request->input('period_end', Carbon::now()->endOfMonth());

        if (is_string($periodStart)) {
            $periodStart = Carbon::parse($periodStart);
        }
        if (is_string($periodEnd)) {
            $periodEnd = Carbon::parse($periodEnd);
        }

        // Calculate total costs per service line
        $totalCosts = $this->calculateTotalCosts($serviceLine, $periodStart, $periodEnd);

        // Calculate costs by category
        $costsByCategory = $this->calculateCostsByCategory($serviceLine, $periodStart, $periodEnd);

        // Calculate costs by cost center
        $costsByCostCenter = $this->calculateCostsByCostCenter($serviceLine, $periodStart, $periodEnd);

        return view('costcentermanagement::service-lines.cost-analysis', [
            'serviceLine' => $serviceLine,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
            'totalCosts' => $totalCosts,
            'costsByCategory' => $costsByCategory,
            'costsByCostCenter' => $costsByCostCenter,
        ]);
    }

    /**
     * Display profitability report for a service line.
     */
    public function profitabilityReport(ServiceLine $serviceLine, Request $request)
    {
        $request->validate([
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after_or_equal:period_start',
        ]);

        $periodStart = $request->input('period_start', Carbon::now()->startOfMonth());
        $periodEnd = $request->input('period_end', Carbon::now()->endOfMonth());

        if (is_string($periodStart)) {
            $periodStart = Carbon::parse($periodStart);
        }
        if (is_string($periodEnd)) {
            $periodEnd = Carbon::parse($periodEnd);
        }

        // Calculate total costs
        $totalCosts = $this->calculateTotalCosts($serviceLine, $periodStart, $periodEnd);

        // Calculate total revenue (if available)
        $totalRevenue = $this->calculateTotalRevenue($serviceLine, $periodStart, $periodEnd);

        // Calculate profit margin
        $profitMargin = $totalRevenue > 0 
            ? (($totalRevenue - $totalCosts) / $totalRevenue) * 100 
            : 0;

        return view('costcentermanagement::service-lines.profitability-report', [
            'serviceLine' => $serviceLine,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
            'totalCosts' => $totalCosts,
            'totalRevenue' => $totalRevenue,
            'profitMargin' => $profitMargin,
            'profit' => $totalRevenue - $totalCosts,
        ]);
    }

    /**
     * Display comparative analysis between service lines.
     */
    public function comparativeAnalysis(Request $request)
    {
        $request->validate([
            'service_line_ids' => 'required|array|min:2',
            'service_line_ids.*' => 'exists:service_lines,id',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after_or_equal:period_start',
        ]);

        $serviceLineIds = $request->input('service_line_ids');
        $periodStart = $request->input('period_start', Carbon::now()->startOfMonth());
        $periodEnd = $request->input('period_end', Carbon::now()->endOfMonth());

        if (is_string($periodStart)) {
            $periodStart = Carbon::parse($periodStart);
        }
        if (is_string($periodEnd)) {
            $periodEnd = Carbon::parse($periodEnd);
        }

        $serviceLines = ServiceLine::whereIn('id', $serviceLineIds)->get();

        $comparativeData = $serviceLines->map(function ($serviceLine) use ($periodStart, $periodEnd) {
            $totalCosts = $this->calculateTotalCosts($serviceLine, $periodStart, $periodEnd);
            $totalRevenue = $this->calculateTotalRevenue($serviceLine, $periodStart, $periodEnd);
            $profitMargin = $totalRevenue > 0 
                ? (($totalRevenue - $totalCosts) / $totalRevenue) * 100 
                : 0;

            return [
                'service_line' => $serviceLine,
                'total_costs' => $totalCosts,
                'total_revenue' => $totalRevenue,
                'profit' => $totalRevenue - $totalCosts,
                'profit_margin' => $profitMargin,
            ];
        });

        return view('costcentermanagement::service-lines.comparative-analysis', [
            'comparativeData' => $comparativeData,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
        ]);
    }

    /**
     * Calculate total costs for a service line.
     */
    private function calculateTotalCosts(ServiceLine $serviceLine, Carbon $periodStart, Carbon $periodEnd): float
    {
        $totalCosts = 0;

        foreach ($serviceLine->members as $member) {
            $costCenterCosts = CostCenterTransaction::where('cost_center_id', $member->cost_center_id)
                ->whereBetween('transaction_date', [$periodStart, $periodEnd])
                ->whereIn('transaction_type', ['direct_cost', 'allocated_cost'])
                ->sum('amount');

            // Apply allocation percentage for shared cost centers
            $allocatedCosts = $costCenterCosts * ($member->allocation_percentage / 100);
            $totalCosts += $allocatedCosts;
        }

        return $totalCosts;
    }

    /**
     * Calculate costs by category for a service line.
     */
    private function calculateCostsByCategory(ServiceLine $serviceLine, Carbon $periodStart, Carbon $periodEnd): array
    {
        $costsByCategory = [];

        foreach ($serviceLine->members as $member) {
            $categoryCosts = CostCenterTransaction::where('cost_center_id', $member->cost_center_id)
                ->whereBetween('transaction_date', [$periodStart, $periodEnd])
                ->whereIn('transaction_type', ['direct_cost', 'allocated_cost'])
                ->select('category', DB::raw('SUM(amount) as total'))
                ->groupBy('category')
                ->get();

            foreach ($categoryCosts as $categoryCost) {
                $category = $categoryCost->category;
                $allocatedAmount = $categoryCost->total * ($member->allocation_percentage / 100);

                if (!isset($costsByCategory[$category])) {
                    $costsByCategory[$category] = 0;
                }
                $costsByCategory[$category] += $allocatedAmount;
            }
        }

        return $costsByCategory;
    }

    /**
     * Calculate costs by cost center for a service line.
     */
    private function calculateCostsByCostCenter(ServiceLine $serviceLine, Carbon $periodStart, Carbon $periodEnd): array
    {
        $costsByCostCenter = [];

        foreach ($serviceLine->members as $member) {
            $costCenterCosts = CostCenterTransaction::where('cost_center_id', $member->cost_center_id)
                ->whereBetween('transaction_date', [$periodStart, $periodEnd])
                ->whereIn('transaction_type', ['direct_cost', 'allocated_cost'])
                ->sum('amount');

            $allocatedCosts = $costCenterCosts * ($member->allocation_percentage / 100);

            $costsByCostCenter[] = [
                'cost_center' => $member->costCenter,
                'allocation_percentage' => $member->allocation_percentage,
                'total_costs' => $costCenterCosts,
                'allocated_costs' => $allocatedCosts,
            ];
        }

        return $costsByCostCenter;
    }

    /**
     * Calculate total revenue for a service line.
     */
    private function calculateTotalRevenue(ServiceLine $serviceLine, Carbon $periodStart, Carbon $periodEnd): float
    {
        $totalRevenue = 0;

        foreach ($serviceLine->members as $member) {
            $costCenterRevenue = CostCenterTransaction::where('cost_center_id', $member->cost_center_id)
                ->whereBetween('transaction_date', [$periodStart, $periodEnd])
                ->where('transaction_type', 'revenue')
                ->sum('amount');

            // Apply allocation percentage for shared cost centers
            $allocatedRevenue = $costCenterRevenue * ($member->allocation_percentage / 100);
            $totalRevenue += $allocatedRevenue;
        }

        return $totalRevenue;
    }
}
