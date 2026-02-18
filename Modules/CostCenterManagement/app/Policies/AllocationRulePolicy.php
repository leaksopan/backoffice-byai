<?php

namespace Modules\CostCenterManagement\Policies;

use App\Models\User;
use Modules\CostCenterManagement\Models\AllocationRule;

class AllocationRulePolicy
{
    /**
     * Determine if user can view allocation rule
     */
    public function view(User $user, AllocationRule $allocationRule): bool
    {
        // User dengan permission view bisa lihat semua
        return $user->can('cost-center-management.view');
    }

    /**
     * Determine if user can create allocation rule
     */
    public function create(User $user): bool
    {
        return $user->can('cost-center-management.allocate');
    }

    /**
     * Determine if user can update allocation rule
     */
    public function update(User $user, AllocationRule $allocationRule): bool
    {
        // Hanya bisa edit jika masih draft atau rejected
        if (!in_array($allocationRule->approval_status, ['draft', 'rejected'])) {
            return false;
        }

        return $user->can('cost-center-management.allocate');
    }

    /**
     * Determine if user can delete allocation rule
     */
    public function delete(User $user, AllocationRule $allocationRule): bool
    {
        // Hanya bisa delete jika masih draft atau rejected
        if (!in_array($allocationRule->approval_status, ['draft', 'rejected'])) {
            return false;
        }

        return $user->can('cost-center-management.delete');
    }

    /**
     * Determine if user can approve allocation rule
     */
    public function approve(User $user, AllocationRule $allocationRule): bool
    {
        // Tidak bisa approve rule sendiri
        if ($allocationRule->created_by === $user->id) {
            return false;
        }

        // Hanya bisa approve jika status pending
        if ($allocationRule->approval_status !== 'pending') {
            return false;
        }

        return $user->can('cost-center-management.approve');
    }

    /**
     * Determine if user can submit for approval
     */
    public function submitForApproval(User $user, AllocationRule $allocationRule): bool
    {
        // Hanya bisa submit jika draft atau rejected
        if (!in_array($allocationRule->approval_status, ['draft', 'rejected'])) {
            return false;
        }

        return $user->can('cost-center-management.allocate');
    }
}
