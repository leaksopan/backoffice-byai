<?php

namespace Modules\CostCenterManagement\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\MasterDataManagement\Events\MasterDataUpdated;
use Modules\CostCenterManagement\Models\CostCenter;

class UpdateCostCenterOnOrgUnitChange
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

        // Cari cost center yang terkait dengan organization unit ini
        $costCenters = CostCenter::where('organization_unit_id', $event->entityId)->get();

        if ($costCenters->isEmpty()) {
            return;
        }

        // Update cost center jika ada perubahan yang relevan
        foreach ($costCenters as $costCenter) {
            $needsUpdate = false;
            $updates = [];

            // Jika organization unit di-nonaktifkan, nonaktifkan cost center
            if (in_array('is_active', $event->changedFields) && 
                isset($event->newValues['is_active']) && 
                $event->newValues['is_active'] === false) {
                
                $updates['is_active'] = false;
                $needsUpdate = true;
                
                Log::info('Cost center deactivated due to organization unit deactivation', [
                    'cost_center_id' => $costCenter->id,
                    'organization_unit_id' => $event->entityId,
                ]);
            }

            // Jika ada perubahan nama, bisa update description atau log
            if (in_array('name', $event->changedFields)) {
                Log::info('Organization unit name changed for cost center', [
                    'cost_center_id' => $costCenter->id,
                    'organization_unit_id' => $event->entityId,
                    'old_name' => $event->oldValues['name'] ?? null,
                    'new_name' => $event->newValues['name'] ?? null,
                ]);
            }

            if ($needsUpdate) {
                $costCenter->update($updates);
            }
        }
    }
}
