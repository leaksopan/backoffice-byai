<?php

namespace Modules\MasterDataManagement\Services;

use Carbon\Carbon;
use Modules\MasterDataManagement\Models\MdmTariff;

class TariffCalculationService
{
    public function getApplicableTariff(int $serviceId, string $class, Carbon $date, ?string $payerType = null): ?MdmTariff
    {
        return MdmTariff::active()
            ->forService($serviceId)
            ->forClass($class)
            ->forPayer($payerType)
            ->validOn($date)
            ->with('breakdowns')
            ->first();
    }

    public function validateNoPeriodOverlap(
        int $serviceId,
        string $class,
        Carbon $startDate,
        Carbon $endDate = null,
        ?string $payerType = null,
        ?int $excludeTariffId = null
    ): bool {
        $query = MdmTariff::active()
            ->forService($serviceId)
            ->forClass($class)
            ->forPayer($payerType);

        if ($excludeTariffId) {
            $query->where('id', '!=', $excludeTariffId);
        }

        // Check for overlapping periods
        $query->where(function ($q) use ($startDate, $endDate) {
            $q->where(function ($subQ) use ($startDate, $endDate) {
                // Case 1: Existing period has end_date
                // Overlap if: existing_start <= new_end AND existing_end >= new_start
                $subQ->whereNotNull('end_date')
                    ->where('start_date', '<=', $endDate ?? '9999-12-31')
                    ->where('end_date', '>=', $startDate);
            })->orWhere(function ($subQ) use ($startDate, $endDate) {
                // Case 2: Existing period has no end_date (open-ended/unlimited)
                // Overlap if: new_end >= existing_start (or new has no end)
                $subQ->whereNull('end_date');
                
                if ($endDate) {
                    // New period has end date: overlap if new_end >= existing_start
                    $subQ->where('start_date', '<=', $endDate);
                } else {
                    // Both unlimited: always overlap (can't have two unlimited periods)
                    // No additional condition needed - whereNull already matches
                }
            });
        });

        return $query->doesntExist();
    }

    public function calculateTotalTariff(MdmTariff $tariff): float
    {
        if ($tariff->breakdowns->isEmpty()) {
            return (float) $tariff->tariff_amount;
        }

        return (float) $tariff->breakdowns->sum('amount');
    }
}
