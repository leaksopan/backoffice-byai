<?php

namespace Modules\MasterDataManagement\Services;

use Carbon\Carbon;
use Modules\MasterDataManagement\Models\MdmAsset;

class AssetDepreciationService
{
    /**
     * Calculate monthly depreciation for an asset
     */
    public function calculateMonthlyDepreciation(MdmAsset $asset): float
    {
        if (!$asset->isDepreciable()) {
            return 0.0;
        }

        $depreciableValue = $asset->acquisition_value - $asset->residual_value;
        $totalMonths = $asset->useful_life_years * 12;

        return match ($asset->depreciation_method) {
            'straight_line' => $this->calculateStraightLineMonthly($depreciableValue, $totalMonths),
            'declining_balance' => $this->calculateDecliningBalanceMonthly($asset),
            'units_of_production' => 0.0, // Not implemented yet
            default => 0.0,
        };
    }

    /**
     * Calculate accumulated depreciation up to a specific date
     */
    public function calculateAccumulatedDepreciation(MdmAsset $asset, Carbon $asOfDate): float
    {
        if (!$asset->isDepreciable()) {
            return 0.0;
        }

        $acquisitionDate = Carbon::parse($asset->acquisition_date);
        
        if ($asOfDate->lt($acquisitionDate)) {
            return 0.0;
        }

        $monthsElapsed = $acquisitionDate->diffInMonths($asOfDate);
        $totalMonths = $asset->useful_life_years * 12;
        
        // Cap at total depreciable value
        $monthsElapsed = min($monthsElapsed, $totalMonths);

        return match ($asset->depreciation_method) {
            'straight_line' => $this->calculateStraightLineAccumulated($asset, $monthsElapsed),
            'declining_balance' => $this->calculateDecliningBalanceAccumulated($asset, $monthsElapsed),
            'units_of_production' => 0.0, // Not implemented yet
            default => 0.0,
        };
    }

    /**
     * Get book value of asset at a specific date
     */
    public function getBookValue(MdmAsset $asset, Carbon $asOfDate): float
    {
        $accumulatedDepreciation = $this->calculateAccumulatedDepreciation($asset, $asOfDate);
        $bookValue = $asset->acquisition_value - $accumulatedDepreciation;
        
        // Book value should not go below residual value
        return max($bookValue, $asset->residual_value);
    }

    /**
     * Calculate straight line monthly depreciation
     */
    private function calculateStraightLineMonthly(float $depreciableValue, int $totalMonths): float
    {
        if ($totalMonths <= 0) {
            return 0.0;
        }

        return round($depreciableValue / $totalMonths, 2);
    }

    /**
     * Calculate straight line accumulated depreciation
     */
    private function calculateStraightLineAccumulated(MdmAsset $asset, int $monthsElapsed): float
    {
        $depreciableValue = $asset->acquisition_value - $asset->residual_value;
        $totalMonths = $asset->useful_life_years * 12;
        
        if ($totalMonths <= 0) {
            return 0.0;
        }

        $monthlyDepreciation = $depreciableValue / $totalMonths;
        $accumulated = $monthlyDepreciation * $monthsElapsed;
        
        // Cap at depreciable value
        return min($accumulated, $depreciableValue);
    }

    /**
     * Calculate declining balance monthly depreciation
     * Using double declining balance (200% of straight line rate)
     */
    private function calculateDecliningBalanceMonthly(MdmAsset $asset): float
    {
        $straightLineRate = 1 / $asset->useful_life_years;
        $decliningRate = $straightLineRate * 2; // Double declining
        $monthlyRate = $decliningRate / 12;
        
        // For declining balance, we need current book value
        // This is a simplified calculation for the first month
        $bookValue = $asset->acquisition_value;
        
        return round($bookValue * $monthlyRate, 2);
    }

    /**
     * Calculate declining balance accumulated depreciation
     */
    private function calculateDecliningBalanceAccumulated(MdmAsset $asset, int $monthsElapsed): float
    {
        $straightLineRate = 1 / $asset->useful_life_years;
        $decliningRate = $straightLineRate * 2; // Double declining
        $monthlyRate = $decliningRate / 12;
        
        $bookValue = $asset->acquisition_value;
        $accumulated = 0.0;
        
        for ($i = 0; $i < $monthsElapsed; $i++) {
            $monthlyDepreciation = $bookValue * $monthlyRate;
            $accumulated += $monthlyDepreciation;
            $bookValue -= $monthlyDepreciation;
            
            // Stop if book value reaches residual value
            if ($bookValue <= $asset->residual_value) {
                $accumulated = $asset->acquisition_value - $asset->residual_value;
                break;
            }
        }
        
        return round($accumulated, 2);
    }
}
