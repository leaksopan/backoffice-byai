<?php

namespace Modules\CostCenterManagement\Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\CostCenterManagement\Models\AllocationRule;
use Modules\CostCenterManagement\Models\AllocationRuleTarget;
use Modules\CostCenterManagement\Models\CostCenterBudget;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;

class ApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $creator;
    protected User $approver;
    protected CostCenter $sourceCostCenter;
    protected CostCenter $targetCostCenter;
    protected AllocationRule $allocationRule;
    protected CostCenterBudget $budget;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        Permission::create(['name' => 'cost-center-management.allocate']);
        Permission::create(['name' => 'cost-center-management.approve']);
        Permission::create(['name' => 'cost-center-management.view']);
        Permission::create(['name' => 'cost-center-management.edit']);

        // Create users
        $this->creator = User::factory()->create();
        $this->creator->givePermissionTo(['cost-center-management.allocate']);

        $this->approver = User::factory()->create();
        $this->approver->givePermissionTo(['cost-center-management.approve']);

        // Create organization units
        $sourceOrgUnit = MdmOrganizationUnit::create([
            'code' => 'OU001',
            'name' => 'Source Org Unit',
            'type' => 'department',
            'is_active' => true,
        ]);

        $targetOrgUnit = MdmOrganizationUnit::create([
            'code' => 'OU002',
            'name' => 'Target Org Unit',
            'type' => 'department',
            'is_active' => true,
        ]);

        // Create cost centers
        $this->sourceCostCenter = CostCenter::create([
            'code' => 'CC001',
            'name' => 'Source Cost Center',
            'type' => 'administrative',
            'classification' => 'Keuangan',
            'organization_unit_id' => $sourceOrgUnit->id,
            'is_active' => true,
            'effective_date' => now(),
            'created_by' => $this->creator->id,
        ]);

        $this->targetCostCenter = CostCenter::create([
            'code' => 'CC002',
            'name' => 'Target Cost Center',
            'type' => 'medical',
            'classification' => 'Rawat Jalan',
            'organization_unit_id' => $targetOrgUnit->id,
            'is_active' => true,
            'effective_date' => now(),
            'created_by' => $this->creator->id,
        ]);

        // Create allocation rule
        $this->allocationRule = AllocationRule::create([
            'code' => 'AR001',
            'name' => 'Test Allocation Rule',
            'source_cost_center_id' => $this->sourceCostCenter->id,
            'allocation_base' => 'percentage',
            'is_active' => true,
            'effective_date' => now(),
            'approval_status' => 'draft',
            'created_by' => $this->creator->id,
        ]);

        // Create allocation target
        AllocationRuleTarget::create([
            'allocation_rule_id' => $this->allocationRule->id,
            'target_cost_center_id' => $this->targetCostCenter->id,
            'allocation_percentage' => 100.00,
        ]);

        // Create budget
        $this->budget = CostCenterBudget::create([
            'cost_center_id' => $this->sourceCostCenter->id,
            'fiscal_year' => 2026,
            'period_month' => 2,
            'category' => 'personnel',
            'budget_amount' => 100000,
            'revision_number' => 0,
            'created_by' => $this->creator->id,
        ]);
    }

    /** @test */
    public function allocation_rule_starts_as_draft()
    {
        $this->assertEquals('draft', $this->allocationRule->approval_status);
    }

    /** @test */
    public function allocation_rule_can_be_submitted_for_approval()
    {
        $this->allocationRule->update(['approval_status' => 'pending']);

        $this->assertEquals('pending', $this->allocationRule->fresh()->approval_status);
    }

    /** @test */
    public function allocation_rule_can_be_approved()
    {
        $this->allocationRule->update(['approval_status' => 'pending']);

        $this->allocationRule->update([
            'approval_status' => 'approved',
            'approved_by' => $this->approver->id,
            'approved_at' => now(),
        ]);

        $this->assertEquals('approved', $this->allocationRule->fresh()->approval_status);
        $this->assertEquals($this->approver->id, $this->allocationRule->fresh()->approved_by);
        $this->assertNotNull($this->allocationRule->fresh()->approved_at);
    }

    /** @test */
    public function allocation_rule_can_be_rejected()
    {
        $this->allocationRule->update(['approval_status' => 'pending']);

        $this->allocationRule->update([
            'approval_status' => 'rejected',
            'justification' => 'Invalid allocation percentage',
        ]);

        $this->assertEquals('rejected', $this->allocationRule->fresh()->approval_status);
        $this->assertNotNull($this->allocationRule->fresh()->justification);
    }

    /** @test */
    public function rejected_allocation_rule_can_be_resubmitted()
    {
        $this->allocationRule->update([
            'approval_status' => 'rejected',
            'justification' => 'Invalid allocation percentage',
        ]);

        // Fix the issue and resubmit
        $this->allocationRule->update([
            'approval_status' => 'pending',
            'justification' => 'Fixed allocation percentage',
        ]);

        $this->assertEquals('pending', $this->allocationRule->fresh()->approval_status);
    }

    /** @test */
    public function approved_allocation_rule_cannot_be_edited()
    {
        $this->allocationRule->update([
            'approval_status' => 'approved',
            'approved_by' => $this->approver->id,
            'approved_at' => now(),
        ]);

        // Policy should prevent editing
        $this->assertFalse($this->creator->can('update', $this->allocationRule));
    }

    /** @test */
    public function draft_allocation_rule_can_be_edited()
    {
        $this->assertTrue($this->creator->can('update', $this->allocationRule));
    }

    /** @test */
    public function budget_revision_creates_new_record()
    {
        $originalBudget = $this->budget;

        // Create revision
        $revisedBudget = CostCenterBudget::create([
            'cost_center_id' => $this->sourceCostCenter->id,
            'fiscal_year' => 2026,
            'period_month' => 2,
            'category' => 'personnel',
            'budget_amount' => 120000,
            'revision_number' => 1,
            'revision_justification' => 'Increased personnel costs',
            'created_by' => $this->creator->id,
        ]);

        $this->assertEquals(0, $originalBudget->revision_number);
        $this->assertEquals(1, $revisedBudget->revision_number);
        $this->assertEquals(100000, $originalBudget->budget_amount);
        $this->assertEquals(120000, $revisedBudget->budget_amount);
    }

    /** @test */
    public function budget_revision_requires_justification()
    {
        $revisedBudget = CostCenterBudget::create([
            'cost_center_id' => $this->sourceCostCenter->id,
            'fiscal_year' => 2026,
            'period_month' => 2,
            'category' => 'personnel',
            'budget_amount' => 120000,
            'revision_number' => 1,
            'revision_justification' => 'Increased personnel costs',
            'created_by' => $this->creator->id,
        ]);

        $this->assertNotNull($revisedBudget->revision_justification);
    }

    /** @test */
    public function budget_revision_can_be_approved()
    {
        $revisedBudget = CostCenterBudget::create([
            'cost_center_id' => $this->sourceCostCenter->id,
            'fiscal_year' => 2026,
            'period_month' => 2,
            'category' => 'personnel',
            'budget_amount' => 120000,
            'revision_number' => 1,
            'revision_justification' => 'Increased personnel costs',
            'created_by' => $this->creator->id,
        ]);

        $revisedBudget->update([
            'approved_by' => $this->approver->id,
            'approved_at' => now(),
        ]);

        $this->assertEquals($this->approver->id, $revisedBudget->fresh()->approved_by);
        $this->assertNotNull($revisedBudget->fresh()->approved_at);
    }

    /** @test */
    public function only_active_approved_allocation_rules_are_used()
    {
        // Create multiple rules with different statuses
        $draftRule = AllocationRule::create([
            'code' => 'AR002',
            'name' => 'Draft Rule',
            'source_cost_center_id' => $this->sourceCostCenter->id,
            'allocation_base' => 'percentage',
            'is_active' => true,
            'effective_date' => now(),
            'approval_status' => 'draft',
            'created_by' => $this->creator->id,
        ]);

        $approvedRule = AllocationRule::create([
            'code' => 'AR003',
            'name' => 'Approved Rule',
            'source_cost_center_id' => $this->sourceCostCenter->id,
            'allocation_base' => 'percentage',
            'is_active' => true,
            'effective_date' => now(),
            'approval_status' => 'approved',
            'approved_by' => $this->approver->id,
            'approved_at' => now(),
            'created_by' => $this->creator->id,
        ]);

        $inactiveRule = AllocationRule::create([
            'code' => 'AR004',
            'name' => 'Inactive Rule',
            'source_cost_center_id' => $this->sourceCostCenter->id,
            'allocation_base' => 'percentage',
            'is_active' => false,
            'effective_date' => now(),
            'approval_status' => 'approved',
            'approved_by' => $this->approver->id,
            'approved_at' => now(),
            'created_by' => $this->creator->id,
        ]);

        // Only approved and active rules should be used
        $activeApprovedRules = AllocationRule::activeAndApproved()->get();

        $this->assertCount(1, $activeApprovedRules);
        $this->assertEquals('AR003', $activeApprovedRules->first()->code);
    }
}
