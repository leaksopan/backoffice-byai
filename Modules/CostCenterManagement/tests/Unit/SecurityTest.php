<?php

namespace Modules\CostCenterManagement\Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\CostCenterManagement\Models\AllocationRule;
use Modules\CostCenterManagement\Models\CostCenterBudget;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $viewer;
    protected User $approver;
    protected CostCenter $costCenter;
    protected AllocationRule $allocationRule;
    protected CostCenterBudget $budget;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        Permission::create(['name' => 'cost-center-management.view']);
        Permission::create(['name' => 'cost-center-management.view-all']);
        Permission::create(['name' => 'cost-center-management.create']);
        Permission::create(['name' => 'cost-center-management.edit']);
        Permission::create(['name' => 'cost-center-management.delete']);
        Permission::create(['name' => 'cost-center-management.allocate']);
        Permission::create(['name' => 'cost-center-management.approve']);

        // Create users
        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo([
            'cost-center-management.view-all',
            'cost-center-management.create',
            'cost-center-management.edit',
            'cost-center-management.delete',
            'cost-center-management.allocate',
        ]);

        $this->manager = User::factory()->create();
        $this->manager->givePermissionTo([
            'cost-center-management.view',
            'cost-center-management.edit',
        ]);

        $this->viewer = User::factory()->create();
        $this->viewer->givePermissionTo(['cost-center-management.view']);

        $this->approver = User::factory()->create();
        $this->approver->givePermissionTo([
            'cost-center-management.view-all',
            'cost-center-management.approve',
        ]);

        // Create organization unit
        $orgUnit = MdmOrganizationUnit::create([
            'code' => 'OU001',
            'name' => 'Test Org Unit',
            'type' => 'department',
            'is_active' => true,
        ]);

        // Create cost center
        $this->costCenter = CostCenter::create([
            'code' => 'CC001',
            'name' => 'Test Cost Center',
            'type' => 'medical',
            'classification' => 'Rawat Jalan',
            'organization_unit_id' => $orgUnit->id,
            'manager_user_id' => $this->manager->id,
            'is_active' => true,
            'effective_date' => now(),
            'created_by' => $this->admin->id,
        ]);

        // Create allocation rule
        $this->allocationRule = AllocationRule::create([
            'code' => 'AR001',
            'name' => 'Test Allocation Rule',
            'source_cost_center_id' => $this->costCenter->id,
            'allocation_base' => 'percentage',
            'is_active' => true,
            'effective_date' => now(),
            'approval_status' => 'draft',
            'created_by' => $this->admin->id,
        ]);

        // Create budget
        $this->budget = CostCenterBudget::create([
            'cost_center_id' => $this->costCenter->id,
            'fiscal_year' => 2026,
            'period_month' => 2,
            'category' => 'personnel',
            'budget_amount' => 100000,
            'created_by' => $this->admin->id,
        ]);
    }

    /** @test */
    public function admin_can_view_all_cost_centers()
    {
        $this->assertTrue($this->admin->can('view', $this->costCenter));
    }

    /** @test */
    public function manager_can_only_view_their_own_cost_center()
    {
        $this->assertTrue($this->manager->can('view', $this->costCenter));

        // Create another cost center with different manager
        $otherOrgUnit = MdmOrganizationUnit::create([
            'code' => 'OU002',
            'name' => 'Other Org Unit',
            'type' => 'department',
            'is_active' => true,
        ]);

        $otherCostCenter = CostCenter::create([
            'code' => 'CC002',
            'name' => 'Other Cost Center',
            'type' => 'medical',
            'classification' => 'Rawat Inap',
            'organization_unit_id' => $otherOrgUnit->id,
            'manager_user_id' => $this->admin->id,
            'is_active' => true,
            'effective_date' => now(),
            'created_by' => $this->admin->id,
        ]);

        $this->assertFalse($this->manager->can('view', $otherCostCenter));
    }

    /** @test */
    public function viewer_cannot_edit_cost_center()
    {
        $this->assertFalse($this->viewer->can('update', $this->costCenter));
    }

    /** @test */
    public function manager_can_edit_their_own_cost_center()
    {
        $this->assertTrue($this->manager->can('update', $this->costCenter));
    }

    /** @test */
    public function manager_cannot_delete_cost_center()
    {
        $this->assertFalse($this->manager->can('delete', $this->costCenter));
    }

    /** @test */
    public function admin_can_delete_cost_center()
    {
        $this->assertTrue($this->admin->can('delete', $this->costCenter));
    }

    /** @test */
    public function user_with_allocate_permission_can_create_allocation_rule()
    {
        $this->assertTrue($this->admin->can('create', AllocationRule::class));
    }

    /** @test */
    public function user_cannot_update_approved_allocation_rule()
    {
        $this->allocationRule->update(['approval_status' => 'approved']);
        
        $this->assertFalse($this->admin->can('update', $this->allocationRule));
    }

    /** @test */
    public function user_can_update_draft_allocation_rule()
    {
        $this->assertTrue($this->admin->can('update', $this->allocationRule));
    }

    /** @test */
    public function user_cannot_approve_their_own_allocation_rule()
    {
        $this->allocationRule->update([
            'approval_status' => 'pending',
            'created_by' => $this->approver->id,
        ]);

        $this->assertFalse($this->approver->can('approve', $this->allocationRule));
    }

    /** @test */
    public function approver_can_approve_others_allocation_rule()
    {
        $this->allocationRule->update([
            'approval_status' => 'pending',
            'created_by' => $this->admin->id,
        ]);

        $this->assertTrue($this->approver->can('approve', $this->allocationRule));
    }

    /** @test */
    public function user_cannot_approve_draft_allocation_rule()
    {
        $this->allocationRule->update(['approval_status' => 'draft']);

        $this->assertFalse($this->approver->can('approve', $this->allocationRule));
    }

    /** @test */
    public function user_can_submit_draft_allocation_rule_for_approval()
    {
        $this->assertTrue($this->admin->can('submitForApproval', $this->allocationRule));
    }

    /** @test */
    public function user_cannot_submit_approved_allocation_rule()
    {
        $this->allocationRule->update(['approval_status' => 'approved']);

        $this->assertFalse($this->admin->can('submitForApproval', $this->allocationRule));
    }

    /** @test */
    public function admin_can_view_all_budgets()
    {
        $this->assertTrue($this->admin->can('view', $this->budget));
    }

    /** @test */
    public function manager_can_only_view_their_cost_center_budget()
    {
        $this->assertTrue($this->manager->can('view', $this->budget));

        // Create budget for other cost center
        $otherOrgUnit = MdmOrganizationUnit::create([
            'code' => 'OU003',
            'name' => 'Other Org Unit 2',
            'type' => 'department',
            'is_active' => true,
        ]);

        $otherCostCenter = CostCenter::create([
            'code' => 'CC003',
            'name' => 'Other Cost Center 2',
            'type' => 'medical',
            'classification' => 'IGD',
            'organization_unit_id' => $otherOrgUnit->id,
            'manager_user_id' => $this->admin->id,
            'is_active' => true,
            'effective_date' => now(),
            'created_by' => $this->admin->id,
        ]);

        $otherBudget = CostCenterBudget::create([
            'cost_center_id' => $otherCostCenter->id,
            'fiscal_year' => 2026,
            'period_month' => 2,
            'category' => 'supplies',
            'budget_amount' => 50000,
            'created_by' => $this->admin->id,
        ]);

        $this->assertFalse($this->manager->can('view', $otherBudget));
    }

    /** @test */
    public function manager_can_update_their_cost_center_budget()
    {
        $this->assertTrue($this->manager->can('update', $this->budget));
    }

    /** @test */
    public function approver_can_revise_budget()
    {
        $this->assertTrue($this->approver->can('revise', $this->budget));
    }

    /** @test */
    public function manager_cannot_revise_budget()
    {
        $this->assertFalse($this->manager->can('revise', $this->budget));
    }

    /** @test */
    public function user_without_permission_cannot_access_cost_center()
    {
        $unauthorizedUser = User::factory()->create();
        
        $this->assertFalse($unauthorizedUser->can('view', $this->costCenter));
        $this->assertFalse($unauthorizedUser->can('update', $this->costCenter));
        $this->assertFalse($unauthorizedUser->can('delete', $this->costCenter));
    }
}
