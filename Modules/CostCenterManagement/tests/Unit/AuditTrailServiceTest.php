<?php

namespace Modules\CostCenterManagement\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CostCenterManagement\Services\AuditTrailService;
use Modules\CostCenterManagement\Models\AuditLog;
use Modules\CostCenterManagement\Models\CostCenter;
use App\Models\User;
use Carbon\Carbon;

class AuditTrailServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AuditTrailService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AuditTrailService();
    }

    /** @test */
    public function it_gets_audit_trail_with_filters()
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

        $logs = $this->service->getAuditTrail(
            modelType: CostCenter::class,
            modelId: $costCenter->id
        );

        $this->assertGreaterThan(0, $logs->count());
    }

    /** @test */
    public function it_gets_audit_summary()
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

        $summary = $this->service->getAuditSummary(
            Carbon::now()->subDay(),
            Carbon::now()->addDay()
        );

        $this->assertArrayHasKey('total_events', $summary);
        $this->assertArrayHasKey('by_event', $summary);
        $this->assertArrayHasKey('by_model', $summary);
        $this->assertArrayHasKey('by_user', $summary);
        $this->assertGreaterThan(0, $summary['total_events']);
    }

    /** @test */
    public function it_gets_model_history()
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
        $costCenter->update(['name' => 'Final Cost Center']);

        $history = $this->service->getModelHistory(CostCenter::class, $costCenter->id);

        $this->assertCount(3, $history); // created + 2 updates
        $this->assertEquals('created', $history->first()->event);
        $this->assertEquals('updated', $history->last()->event);
    }

    /** @test */
    public function it_gets_user_activity()
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

        $activity = $this->service->getUserActivity(
            $user->id,
            Carbon::now()->subDay(),
            Carbon::now()->addDay()
        );

        $this->assertGreaterThan(0, $activity->count());
        $this->assertEquals($user->id, $activity->first()->user_id);
    }

    /** @test */
    public function it_exports_audit_trail_to_csv()
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

        $csv = $this->service->exportAuditTrail(
            Carbon::now()->subDay(),
            Carbon::now()->addDay(),
            'csv'
        );

        $this->assertStringContainsString('Timestamp,Event,Model Type', $csv);
        $this->assertStringContainsString('created', $csv);
        $this->assertStringContainsString('CC001', $csv);
    }

    /** @test */
    public function it_exports_audit_trail_to_json()
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

        $json = $this->service->exportAuditTrail(
            Carbon::now()->subDay(),
            Carbon::now()->addDay(),
            'json'
        );

        $data = json_decode($json, true);
        $this->assertIsArray($data);
        $this->assertGreaterThan(0, count($data));
    }

    /** @test */
    public function it_throws_exception_for_unsupported_export_format()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->exportAuditTrail(
            Carbon::now()->subDay(),
            Carbon::now()->addDay(),
            'pdf'
        );
    }

    /** @test */
    public function it_cleans_up_old_logs()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create old audit log
        $oldLog = AuditLog::create([
            'auditable_type' => CostCenter::class,
            'auditable_id' => 1,
            'event' => 'created',
            'new_values' => ['test' => 'data'],
            'user_id' => $user->id,
            'created_at' => Carbon::now()->subYears(6),
        ]);

        // Create recent audit log
        $costCenter = CostCenter::create([
            'code' => 'CC001',
            'name' => 'Test Cost Center',
            'type' => 'medical',
            'organization_unit_id' => 1,
            'is_active' => true,
            'effective_date' => now(),
        ]);

        $deletedCount = $this->service->cleanupOldLogs(5);

        $this->assertEquals(1, $deletedCount);
        $this->assertDatabaseMissing('cost_center_audit_logs', ['id' => $oldLog->id]);
        $this->assertDatabaseHas('cost_center_audit_logs', [
            'auditable_type' => CostCenter::class,
            'auditable_id' => $costCenter->id,
        ]);
    }

    /** @test */
    public function it_formats_changes_correctly()
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

        $costCenter->update([
            'name' => 'Updated Cost Center',
            'is_active' => false,
        ]);

        $csv = $this->service->exportAuditTrail(
            Carbon::now()->subDay(),
            Carbon::now()->addDay(),
            'csv'
        );

        $this->assertStringContainsString('name:', $csv);
        $this->assertStringContainsString('Test Cost Center', $csv);
        $this->assertStringContainsString('Updated Cost Center', $csv);
    }
}
