<?php

namespace Modules\CostCenterManagement\Policies;

use App\Models\User;
use Modules\CostCenterManagement\Models\CostCenterBudget;

class CostCenterBudgetPolicy
{
    /**
     * Determine if user can view budget
     */
    public function view(User $user, CostCenterBudget $budget): bool
    {
        // Admin atau user dengan permission view-all bisa lihat semua
        if ($user->can('cost-center-management.view-all')) {
            return true;
        }
        
        // Cost center manager hanya bisa lihat budget cost center yang dikelolanya
        if ($user->can('cost-center-management.view')) {
            return $budget->costCenter->manager_user_id === $user->id;
        }
        
        return false;
    }

    /**
     * Determine if user can create budget
     */
    public function create(User $user): bool
    {
        return $user->can('cost-center-management.create');
    }

    /**
     * Determine if user can update budget
     */
    public function update(User $user, CostCenterBudget $budget): bool
    {
        if ($user->can('cost-center-management.edit')) {
            // Admin bisa edit semua
            if ($user->can('cost-center-management.view-all')) {
                return true;
            }
            
            // Manager hanya bisa edit budget cost center-nya
            return $budget->costCenter->manager_user_id === $user->id;
        }
        
        return false;
    }

    /**
     * Determine if user can delete budget
     */
    public function delete(User $user, CostCenterBudget $budget): bool
    {
        return $user->can('cost-center-management.delete');
    }

    /**
     * Determine if user can revise budget
     */
    public function revise(User $user, CostCenterBudget $budget): bool
    {
        // Revisi budget memerlukan permission khusus
        return $user->can('cost-center-management.approve');
    }
}
