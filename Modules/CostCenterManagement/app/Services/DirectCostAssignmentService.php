<?php

namespace Modules\CostCenterManagement\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\CostCenterManagement\Models\CostCenterTransaction;
use Modules\MasterDataManagement\Models\MdmHrAssignment;
use Modules\MasterDataManagement\Models\MdmAsset;

class DirectCostAssignmentService
{
    /**
     * Assign salary costs to cost centers based on HR assignments.
     *
     * @param int $hrId
     * @param float $salaryAmount
     * @param Carbon $transactionDate
     * @param string|null $description
     * @return array Array of created transactions
     * @throws \Exception
     */
    public function assignSalaryCost(
        int $hrId,
        float $salaryAmount,
        Carbon $transactionDate,
        ?string $description = null
    ): array {
        // Get active HR assignments for the given date
        $assignments = MdmHrAssignment::where('hr_id', $hrId)
            ->where('is_active', true)
            ->whereDate('start_date', '<=', $transactionDate)
            ->where(function ($query) use ($transactionDate) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $transactionDate);
            })
            ->get();

        if ($assignments->isEmpty()) {
            throw new \Exception("Tidak ada penugasan aktif untuk HR ID {$hrId} pada tanggal {$transactionDate->format('Y-m-d')}");
        }

        $transactions = [];
        $totalPercentage = $assignments->sum('allocation_percentage');

        if ($totalPercentage != 100) {
            throw new \Exception("Total alokasi persentase tidak sama dengan 100% (saat ini: {$totalPercentage}%)");
        }

        DB::beginTransaction();
        try {
            foreach ($assignments as $assignment) {
                // Get cost center from organization unit
                $costCenter = CostCenter::where('organization_unit_id', $assignment->unit_id)
                    ->where('is_active', true)
                    ->first();

                if (!$costCenter) {
                    throw new \Exception("Cost center tidak ditemukan untuk unit organisasi ID {$assignment->unit_id}");
                }

                $allocatedAmount = round(($salaryAmount * $assignment->allocation_percentage) / 100, 2);

                $transaction = CostCenterTransaction::create([
                    'cost_center_id' => $costCenter->id,
                    'transaction_date' => $transactionDate,
                    'transaction_type' => 'direct_cost',
                    'category' => 'personnel',
                    'amount' => $allocatedAmount,
                    'reference_type' => 'salary',
                    'reference_id' => $hrId,
                    'description' => $description ?? "Alokasi gaji HR ID {$hrId} ({$assignment->allocation_percentage}%)",
                    'posted_by' => auth()->id(),
                    'posted_at' => now(),
                ]);

                $transactions[] = $transaction;
            }

            DB::commit();
            return $transactions;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Assign depreciation costs to cost centers based on asset location.
     *
     * @param int $assetId
     * @param float $depreciationAmount
     * @param Carbon $transactionDate
     * @param string|null $description
     * @return CostCenterTransaction
     * @throws \Exception
     */
    public function assignDepreciationCost(
        int $assetId,
        float $depreciationAmount,
        Carbon $transactionDate,
        ?string $description = null
    ): CostCenterTransaction {
        $asset = MdmAsset::find($assetId);

        if (!$asset) {
            throw new \Exception("Asset tidak ditemukan dengan ID {$assetId}");
        }

        if (!$asset->is_active) {
            throw new \Exception("Asset ID {$assetId} tidak aktif");
        }

        if (!$asset->current_location_id) {
            throw new \Exception("Asset ID {$assetId} tidak memiliki lokasi");
        }

        // Get cost center from asset location
        $costCenter = CostCenter::where('organization_unit_id', $asset->current_location_id)
            ->where('is_active', true)
            ->first();

        if (!$costCenter) {
            throw new \Exception("Cost center tidak ditemukan untuk lokasi asset (unit organisasi ID {$asset->current_location_id})");
        }

        return CostCenterTransaction::create([
            'cost_center_id' => $costCenter->id,
            'transaction_date' => $transactionDate,
            'transaction_type' => 'direct_cost',
            'category' => 'depreciation',
            'amount' => $depreciationAmount,
            'reference_type' => 'depreciation',
            'reference_id' => $assetId,
            'description' => $description ?? "Depresiasi asset {$asset->code} - {$asset->name}",
            'posted_by' => auth()->id(),
            'posted_at' => now(),
        ]);
    }

    /**
     * Assign material/purchase costs to a cost center.
     *
     * @param int $costCenterId
     * @param float $amount
     * @param Carbon $transactionDate
     * @param string $referenceType
     * @param int|null $referenceId
     * @param string|null $description
     * @return CostCenterTransaction
     * @throws \Exception
     */
    public function assignMaterialCost(
        int $costCenterId,
        float $amount,
        Carbon $transactionDate,
        string $referenceType = 'purchase',
        ?int $referenceId = null,
        ?string $description = null
    ): CostCenterTransaction {
        $costCenter = CostCenter::find($costCenterId);

        if (!$costCenter) {
            throw new \Exception("Cost center tidak ditemukan dengan ID {$costCenterId}");
        }

        if (!$costCenter->is_active) {
            throw new \Exception("Cost center ID {$costCenterId} tidak aktif dan tidak dapat menerima transaksi baru");
        }

        return CostCenterTransaction::create([
            'cost_center_id' => $costCenterId,
            'transaction_date' => $transactionDate,
            'transaction_type' => 'direct_cost',
            'category' => 'supplies',
            'amount' => $amount,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description ?? "Pembelian material",
            'posted_by' => auth()->id(),
            'posted_at' => now(),
        ]);
    }

    /**
     * Validate that a cost center is active before posting transaction.
     *
     * @param int $costCenterId
     * @return bool
     * @throws \Exception
     */
    public function validateActiveCostCenter(int $costCenterId): bool
    {
        $costCenter = CostCenter::find($costCenterId);

        if (!$costCenter) {
            throw new \Exception("Cost center tidak ditemukan dengan ID {$costCenterId}");
        }

        if (!$costCenter->is_active) {
            throw new \Exception("Cost center ID {$costCenterId} tidak aktif dan tidak dapat menerima transaksi baru");
        }

        return true;
    }
}

