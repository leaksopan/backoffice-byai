<?php

namespace Modules\CostCenterManagement\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CostCenterManagement\Models\AuditLog;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\CostCenterManagement\Models\AllocationRule;
use Modules\CostCenterManagement\Models\CostCenterBudget;
use App\Models\User;
use Carbon\Carbon;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create organization unit for FK constraint
        \Modules\MasterDataManagement\Models\MdmOrganizationUnit::create([
            'code' => 'ORG001',
            'name' => 'Test Organization Unit',
            'type' => 'unit',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_creates_audit_log_when_cost_center_is_created()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $costCenter = CostCenter::create([
            'code' => 'CC001',
            'name' => 'Test Cost Center',
            'type' => 'medical',
            'organization_unit_id' => 1,
            'is_active' => true,
            'effective_date' => now(),
        ]);

        $this->assertDatabaseHas('cost_center_audit_logs', [
            'auditable_type' => CostCenter::class,
            'auditable_id' => $costCenter->id,
            'event' => 'created',
            'user_id' => $user->id,
        ]);

        $auditLog = AuditLog::where('auditable_id', $costCenter->id)->first();
        $this->assertNotNull($auditLog->new_values);
        $this->assertEquals('CC001', $auditLog->new_values['code']);
    }

    /** @test */
    public function it_creates_audit_log_when_cost_center_is_updated()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $costCenter = CostCenter::create([
            'code' => 'CC001',
            'name' => 'Test Cost Center',
            'type' => 'medical',
            'organization_unit_id' => 1,
            'is_active' => true,
            'effective_date' => now(),
        ]);

        // Clear previous audit logs
        AuditLog::truncate();

        $costCenter->update(['name' => 'Updated Cost Center']);

        $this->assertDatabaseHas('cost_center_audit_logs', [
            'auditable_type' => CostCenter::class,
            'auditable_id' => $costCenter->id,
            'event' => 'updated',
            'user_id' => $user->id,
        ]);

        $auditLog = AuditLog::where('auditable_id', $costCenter->id)->first();
        $this->assertEquals('Test Cost Center', $auditLog->old_values['name']);
        $this->assertEquals('Updated Cost Center', $auditLog->new_values['name']);
    }

    /** @test */
    public function it_creates_audit_log_when_cost_center_is_deleted()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $costCenter = CostCenter::create([
            'code' => 'CC001',
            'name' => 'Test Cost Center',
            'type' => 'medical',
            'organization_unit_id' => 1,
            'is_active' => true,
            'effective_date' => now(),
        ]);

        $costCenterId = $costCenter->id;

        // Clear previous audit logs
        AuditLog::truncate();

        $costCenter->delete();

        $this->assertDatabaseHas('cost_center_audit_logs', [
            'auditable_type' => CostCenter::class,
            'auditable_id' => $costCenterId,
            'event' => 'deleted',
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function it_creates_audit_log_when_allocation_rule_is_created()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $costCenter = CostCenter::create([
            'code' => 'CC001',
            'name' => 'Test Cost Center',
            'type' => 'medical',
            'organization_unit_id' => 1,
            'is_active' => true,
            'effective_date' => now(),
        ]);

        $allocationRule = AllocationRule::create([
            'code' => 'AR001',
            'name' => 'Test Allocation Rule',
            'source_cost_center_id' => $costCenter->id,
            'allocation_base' => 'percentage',
            'is_active' => true,
            'effective_date' => now(),
        ]);

        $this->assertDatabaseHas('cost_center_audit_logs', [
            'auditable_type' => AllocationRule::class,
            'auditable_id' => $allocationRule->id,
            'event' => 'created',
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function it_creates_audit_log_when_budget_is_updated()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $costCenter = CostCenter::create([
            'code' => 'CC001',
            'name' => 'Test Cost Center',
            'type' => 'medical',
            'organization_unit_id' => 1,
            'is_active' => true,
            'effective_date' => now(),
        ]);

        $budget = CostCenterBudget::create([
            'cost_center_id' => $costCenter->id,
            'fiscal_year' => 2026,
            'period_month' => 1,
            'category' => 'personnel',
            'budget_amount' => 100000,
        ]);

        // Clear previous audit logs
        AuditLog::truncate();

        $budget->update(['budget_amount' => 120000]);

        $this->assertDatabaseHas('cost_center_audit_logs', [
            'auditable_type' => CostCenterBudget::class,
            'auditable_id' => $budget->id,
            'event' => 'updated',
            'user_id' => $user->id,
        ]);

        $auditLog = AuditLog::where('auditable_id', $budget->id)->first();
        $this->assertEquals('100000.00', $auditLog->old_values['budget_amount']);
        $this->assertEquals('120000.00', $auditLog->new_values['budget_amount']);
    }

    /** @test */
    public function it_records_custom_audit_event()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $costCenter = CostCenter::create([
            'code' => 'CC001',
            'name' => 'Test Cost Center',
            'type' => 'medical',
            'organization_unit_id' => 1,
            'is_active' => true,
            'effective_date' => now(),
        ]);

        $costCenter->auditCustomEvent('approved', [
            'old' => ['approval_status' => 'pending'],
            'new' => ['approval_status' => 'approved'],
        ], 'Approved by manager');

        $this->assertDatabaseHas('cost_center_audit_logs', [
            'auditable_type' => CostCenter::class,
            'auditable_id' => $costCenter->id,
            'event' => 'approved',
            'justification' => 'Approved by manager',
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function it_captures_ip_address_and_user_agent()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $costCenter = CostCenter::create([
            'code' => 'CC001',
            'name' => 'Test Cost Center',
            'type' => 'medical',
            'organization_unit_id' => 1,
            'is_active' => true,
            'effective_date' => now(),
        ]);

        $auditLog = AuditLog::where('auditable_id', $costCenter->id)->first();
        $this->assertNotNull($auditLog->ip_address);
        $this->assertNotNull($auditLog->user_agent);
    }

    /** @test */
    public function it_gets_changed_fields_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $costCenter = CostCenter::create([
            'code' => 'CC001',
            'name' => 'Test Cost Center',
            'type' => 'medical',
            'organization_unit_id' => 1,
            'is_active' => true,
            'effective_date' => now(),
        ]);

        // Clear previous audit logs
        AuditLog::truncate();

        $costCenter->update([
            'name' => 'Updated Cost Center',
            'is_active' => false,
        ]);

        $auditLog = AuditLog::where('auditable_id', $costCenter->id)->first();
        $changes = $auditLog->getChangedFields();

        $this->assertArrayHasKey('name', $changes);
        $this->assertArrayHasKey('is_active', $changes);
        $this->assertEquals('Test Cost Center', $changes['name']['old']);
        $this->assertEquals('Updated Cost Center', $changes['name']['new']);
        $this->assertEquals(true, $changes['is_active']['old']);
        $this->assertEquals(false, $changes['is_active']['new']);
    }

    /** @test */
    public function it_does_not_create_audit_log_when_no_changes()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $costCenter = CostCenter::create([
            'code' => 'CC001',
            'name' => 'Test Cost Center',
            'type' => 'medical',
            'organization_unit_id' => 1,
            'is_active' => true,
            'effective_date' => now(),
        ]);

        // Clear previous audit logs
        AuditLog::truncate();

        // Update with same values
        $costCenter->update(['name' => 'Test Cost Center']);

        $this->assertDatabaseMissing('cost_center_audit_logs', [
            'auditable_type' => CostCenter::class,
            'auditable_id' => $costCenter->id,
            'event' => 'updated',
        ]);
    }

    /** @test */
    public function it_can_query_audit_logs_by_model()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $costCenter1 = CostCenter::create([
            'code' => 'CC001',
            'name' => 'Cost Center 1',
            'type' => 'medical',
            'organization_unit_id' => 1,
            'is_active' => true,
            'effective_date' => now(),
        ]);

        $costCenter2 = CostCenter::create([
            'code' => 'CC002',
            'name' => 'Cost Center 2',
            'type' => 'medical',
            'organization_unit_id' => 2,
            'is_active' => true,
            'effective_date' => now(),
        ]);

        $logs = AuditLog::forModel(CostCenter::class, $costCenter1->id)->get();

        $this->assertCount(1, $logs);
        $this->assertEquals($costCenter1->id, $logs->first()->auditable_id);
    }

    /** @test */
    public function it_can_query_audit_logs_by_event()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $costCenter = CostCenter::create([
            'code' => 'CC001',
            'name' => 'Test Cost Center',
            'type' => 'medical',
            'organization_unit_id' => 1,
            'is_active' => true,
            'effective_date' => now(),
        ]);

        $costCenter->update(['name' => 'Updated Cost Center']);

        $createdLogs = AuditLog::forEvent('created')->get();
        $updatedLogs = AuditLog::forEvent('updated')->get();

        $this->assertGreaterThan(0, $createdLogs->count());
        $this->assertGreaterThan(0, $updatedLogs->count());
    }

    /** @test */
    public function it_can_query_audit_logs_by_date_range()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $startDate = Carbon::now()->subDays(7);
        $endDate = Carbon::now();

        $costCenter = CostCenter::create([
            'code' => 'CC001',
            'name' => 'Test Cost Center',
            'type' => 'medical',
            'organization_unit_id' => 1,
            'is_active' => true,
            'effective_date' => now(),
        ]);

        $logs = AuditLog::dateRange($startDate, $endDate)->get();

        $this->assertGreaterThan(0, $logs->count());
    }
}
