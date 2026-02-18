<?php

namespace Modules\CostCenterManagement\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\MasterDataManagement\Events\MasterDataUpdated;

class ReallocateCostOnHRAssignmentChange
{
    /**
     * Handle the event.
     */
    public function handle(MasterDataUpdated $event): void
    {
        // Hanya proses jika entity type adalah hr_assignment
        if ($event->entityType !== 'hr_assignment') {
            return;
        }

        // Cek apakah ada perubahan pada cost_center_id atau allocation_percentage
        $relevantFields = ['cost_center_id', 'allocation_percentage'];
        $hasRelevantChanges = !empty(array_intersect($event->changedFields, $relevantFields));

        if (!$hasRelevantChanges) {
            return;
        }

        // Log perubahan untuk reallocation
        Log::info('HR assignment changed, cost reallocation may be needed', [
            'hr_assignment_id' => $event->entityId,
            'changed_fields' => $event->changedFields,
            'old_values' => array_intersect_key($event->oldValues, array_flip($relevantFields)),
            'new_values' => array_intersect_key($event->newValues, array_flip($relevantFields)),
        ]);

        // TODO: Implementasi logic untuk recalculate direct cost allocation
        // Ini akan dipanggil oleh DirectCostAssignmentService untuk reallocate gaji
        // berdasarkan HR assignment percentage yang baru
        
        // Contoh: Jika ada salary transaction yang belum di-post untuk periode berjalan,
        // perlu di-recalculate berdasarkan assignment percentage yang baru
    }
}
