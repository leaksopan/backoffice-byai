<?php

namespace Modules\CostCenterManagement\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\CostCenterManagement\Models\CostCenterBudget;
use Modules\CostCenterManagement\Models\AllocationRule;
use Modules\CostCenterManagement\Events\BudgetThresholdExceeded;
use Modules\CostCenterManagement\Events\AllocationCompleted;
use Modules\CostCenterManagement\Events\AllocationRuleApprovalRequested;
use Modules\CostCenterManagement\Events\BudgetRevisionApprovalRequested;
use Modules\CostCenterManagement\Notifications\BudgetWarningNotification;
use Modules\CostCenterManagement\Notifications\AllocationCompletedNotification;
use Modules\CostCenterManagement\Notifications\AllocationRuleApprovalRequestedNotification;
use Modules\CostCenterManagement\Notifications\BudgetRevisionApprovalRequestedNotification;
use App\Models\User;
use Spatie\Permission\Models\Permission;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        Permission::create(['name' => 'cost-center-management.approve']);
        Permission::create(['name' => 'cost-center-management.allocate']);

        // Create organization unit untuk foreign key
        \Modules\MasterDataManagement\Models\MdmOrganizationUnit::create([
            'code' => 'ORG-001',
            'name' => 'Test Organization Unit',
            'type' => 'installation',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_sends_budget_warning_notification_when_threshold_exceeded()
    {
        Notification::fake();

        // Create user dengan permission
        $approver = User::factory()->create();
        $approver->givePermissionTo('cost-center-management.approve');

        // Create cost center dengan manager
        $manager = User::factory()->create();
        $costCenter = CostCenter::factory()->create([
            'manager_user_id' => $manager->id,
        ]);

        // Create budget
        $budget = CostCenterBudget::factory()->create([
            'cost_center_id' => $costCenter->id,
            'budget_amount' => 1000000,
            'actual_amount' => 850000,
            'utilization_percentage' => 85.0,
        ]);

        // Dispatch event
        event(new BudgetThresholdExceeded($budget));

        // Assert notification sent to manager
        Notification::assertSentTo($manager, BudgetWarningNotification::class);

        // Assert notification sent to approvers
        Notification::assertSentTo($approver, BudgetWarningNotification::class);
    }

    /** @test */
    public function it_sends_allocation_completed_notification()
    {
        Notification::fake();

        // Create users dengan permissions
        $allocator = User::factory()->create();
        $allocator->givePermissionTo('cost-center-management.allocate');

        $approver = User::factory()->create();
        $approver->givePermissionTo('cost-center-management.approve');

        // Dispatch event
        $batchId = 'ALLOC-20260216-ABC123';
        $totalJournals = 10;
        $totalAmount = 5000000.00;
        $summary = [
            'rules_processed' => 5,
            'period_start' => '2026-02-01',
            'period_end' => '2026-02-28',
        ];

        event(new AllocationCompleted($batchId, $totalJournals, $totalAmount, $summary));

        // Assert notification sent to allocators
        Notification::assertSentTo($allocator, AllocationCompletedNotification::class);

        // Assert notification sent to approvers
        Notification::assertSentTo($approver, AllocationCompletedNotification::class);
    }

    /** @test */
    public function it_sends_allocation_rule_approval_requested_notification()
    {
        Notification::fake();

        // Create user dengan permission
        $approver = User::factory()->create();
        $approver->givePermissionTo('cost-center-management.approve');

        $requester = User::factory()->create();

        // Create allocation rule
        $sourceCostCenter = CostCenter::factory()->create();
        $allocationRule = AllocationRule::factory()->create([
            'source_cost_center_id' => $sourceCostCenter->id,
            'approval_status' => 'pending',
        ]);

        // Dispatch event
        event(new AllocationRuleApprovalRequested($allocationRule, $requester->id));

        // Assert notification sent to approvers
        Notification::assertSentTo($approver, AllocationRuleApprovalRequestedNotification::class);
    }

    /** @test */
    public function it_sends_budget_revision_approval_requested_notification()
    {
        Notification::fake();

        // Create user dengan permission
        $approver = User::factory()->create();
        $approver->givePermissionTo('cost-center-management.approve');

        $requester = User::factory()->create();

        // Create budget
        $costCenter = CostCenter::factory()->create();
        $budget = CostCenterBudget::factory()->create([
            'cost_center_id' => $costCenter->id,
            'revision_number' => 1,
        ]);

        $justification = 'Budget perlu direvisi karena ada perubahan kebutuhan operasional';

        // Dispatch event
        event(new BudgetRevisionApprovalRequested($budget, $requester->id, $justification));

        // Assert notification sent to approvers
        Notification::assertSentTo($approver, BudgetRevisionApprovalRequestedNotification::class);
    }

    /** @test */
    public function budget_warning_notification_contains_correct_data()
    {
        Notification::fake();

        $manager = User::factory()->create();
        $costCenter = CostCenter::factory()->create([
            'manager_user_id' => $manager->id,
        ]);

        $budget = CostCenterBudget::factory()->create([
            'cost_center_id' => $costCenter->id,
            'budget_amount' => 1000000,
            'actual_amount' => 850000,
            'utilization_percentage' => 85.0,
            'variance_amount' => -150000,
        ]);

        event(new BudgetThresholdExceeded($budget));

        // Assert notification was sent
        Notification::assertSentTo($manager, BudgetWarningNotification::class);
        
        // Verify notification data
        Notification::assertSentTo($manager, BudgetWarningNotification::class, function ($notification) use ($budget, $manager) {
            $data = $notification->toArray($manager);
            
            return $data['budget_id'] === $budget->id
                && $data['utilization_percentage'] == 85.0
                && $data['budget_amount'] == 1000000.0
                && $data['actual_amount'] == 850000.0;
        });
    }

    /** @test */
    public function allocation_completed_notification_contains_correct_data()
    {
        Notification::fake();

        $allocator = User::factory()->create();
        $allocator->givePermissionTo('cost-center-management.allocate');

        $batchId = 'ALLOC-20260216-ABC123';
        $totalJournals = 10;
        $totalAmount = 5000000.00;
        $summary = ['rules_processed' => 5];

        event(new AllocationCompleted($batchId, $totalJournals, $totalAmount, $summary));

        // Assert notification was sent
        Notification::assertSentTo($allocator, AllocationCompletedNotification::class);
        
        // Verify notification data
        Notification::assertSentTo($allocator, AllocationCompletedNotification::class, function ($notification) use ($batchId, $totalJournals, $totalAmount, $allocator) {
            $data = $notification->toArray($allocator);
            
            return $data['batch_id'] === $batchId
                && $data['total_journals'] === $totalJournals
                && $data['total_amount'] === $totalAmount;
        });
    }
}
