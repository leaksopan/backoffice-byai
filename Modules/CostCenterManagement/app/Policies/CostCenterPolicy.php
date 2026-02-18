<?php

namespace Modules\CostCenterManagement\Policies;

use App\Models\User;
use Modules\CostCenterManagement\Models\CostCenter;

class CostCenterPolicy
{
    /**
     * Determine if user can view cost center
     */
    public function view(User $user, CostCenter $costCenter): bool
    {
        // Admin atau user dengan permission view-all bisa lihat semua
        if ($user->can('cost-center-management.view-all')) {
            return true;
        }
        
        // Cost center manager hanya bisa lihat cost center yang dikelolanya
        if ($user->can('cost-center-management.view')) {
            return $costCenter->manager_user_id === $user->id;
        }
        
        return false;
    }

    /**
     * Determine if user can create cost center
     */
    public function create(User $user): bool
    {
        return $user->can('cost-center-management.create');
    }

    /**
     * Determine if user can update cost center
     */
    public function update(User $user, CostCenter $costCenter): bool
    {
        if ($user->can('cost-center-management.edit')) {
            // Admin bisa edit semua
            if ($user->can('cost-center-management.view-all')) {
                return true;
            }
            
            // Manager hanya bisa edit cost center-nya
            return $costCenter->manager_user_id === $user->id;
        }
        
        return false;
    }

    /**
     * Determine if user can delete cost center
     */
    public function delete(User $user, CostCenter $costCenter): bool
    {
        return $user->can('cost-center-management.delete');
    }
}
