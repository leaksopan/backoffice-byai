<?php

namespace Modules\CostCenterManagement\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\MasterDataManagement\Events\MasterDataUpdated;
use Modules\CostCenterManagement\Models\CostCenter;

class DeactivateCostCenterOnOrgUnitDeactivation
{
    /**
     * Handle the event.
     */
    public function handle(MasterDataUpdated $event): void
    {
        // Hanya proses jika entity type adalah organization_unit
        if ($event->entityType !== 'organization_unit') {
            return;
        }

        // Cek apakah is_active berubah menjadi false
        if (!in_array('is_active', $event->changedFields)) {
            return;
        }

        if (!isset($event->newValues['is_active']) || $event->newValues['is_active'] !== false) {
            return;
        }

        // Cari semua cost center yang terkait dengan organization unit ini
        $costCenters = CostCenter::where('organization_unit_id', $event->entityId)
            ->where('is_active', true)
            ->get();

        if ($costCenters->isEmpty()) {
            return;
        }

        // Deactivate semua cost center yang terkait
        foreach ($costCenters as $costCenter) {
            $costCenter->update(['is_active' => false]);
            
            Log::info('Cost center deactivated due to organization unit deactivation', [
                'cost_center_id' => $costCenter->id,
                'cost_center_code' => $costCenter->code,
                'organization_unit_id' => $event->entityId,
                'user_id' => $event->userId,
            ]);
        }

        Log::info('Cascade deactivation completed', [
            'organization_unit_id' => $event->entityId,
            'deactivated_cost_centers_count' => $costCenters->count(),
        ]);
    }
}
