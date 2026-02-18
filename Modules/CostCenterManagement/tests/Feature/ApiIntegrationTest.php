<?php

namespace Modules\CostCenterManagement\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\CostCenterManagement\Models\CostCenterTransaction;
use Modules\CostCenterManagement\Models\CostCenterBudget;
use Modules\CostCenterManagement\Models\ServiceLine;
use Modules\CostCenterManagement\Models\ServiceLineMember;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Carbon\Carbon;

class ApiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $unauthorizedUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        Permission::create(['name' => 'access cost-center-management']);
        Permission::create(['name' => 'cost-center-management.view']);
        Permission::create(['name' => 'cost-center-management.create']);
        Permission::create(['name' => 'cost-center-management.edit']);
        Permission::create(['name' => 'cost-center-management.delete']);
        Permission::create(['name' => 'cost-center-management.allocate']);

        // Create authorized user
        $this->user = User::factory()->create();
        $this->user->givePermissionTo([
            'access cost-center-management',
            'cost-center-management.view',
            'cost-center-management.create',
            'cost-center-management.allocate',
        ]);

        // Create unauthorized user
        $this->unauthorizedUser = User::factory()->create();
    }

    /** @test */
    public function it_can_get_cost_centers_list()
    {
        $this->actingAs($this->user);

        $orgUnit = MdmOrganizationUnit::factory()->create();
        $costCenter = CostCenter::factory()->create([
            'organization_unit_id' => $orgUnit->id,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/cost-center-management/cost-centers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data' => [
                        '*' => ['id', 'code', 'name', 'type', 'is_active'],
                    ],
                ],
                'meta' => ['request_id', 'timestamp'],
            ])
            ->assertJson([
                'success' => true,
            ]);
    }

    /** @test */
    public function it_requires_authentication_for_api_access()
    {
        $response = $this->getJson('/api/v1/cost-center-management/cost-centers');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_requires_permission_for_api_access()
    {
        $this->actingAs($this->unauthorizedUser);

        $response = $this->getJson('/api/v1/cost-center-management/cost-centers');

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_create_transaction_via_api()
    {
        $this->actingAs($this->user);

        $orgUnit = MdmOrganizationUnit::factory()->create();
        $costCenter = CostCenter::factory()->create([
            'organization_unit_id' => $orgUnit->id,
            'is_active' => true,
        ]);

        $data = [
            'cost_center_id' => $costCenter->id,
            'transaction_date' => Carbon::now()->toDateString(),
            'transaction_type' => 'direct_cost',
            'category' => 'supplies',
            'amount' => 1000000,
            'description' => 'Test transaction',
        ];

        $response = $this->postJson('/api/v1/cost-center-management/transactions', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('cost_center_transactions', [
            'cost_center_id' => $costCenter->id,
            'amount' => 1000000,
        ]);
    }

    /** @test */
    public function it_rejects_transaction_for_inactive_cost_center()
    {
        $this->actingAs($this->user);

        $orgUnit = MdmOrganizationUnit::factory()->create();
        $costCenter = CostCenter::factory()->create([
            'organization_unit_id' => $orgUnit->id,
            'is_active' => false,
        ]);

        $data = [
            'cost_center_id' => $costCenter->id,
            'transaction_date' => Carbon::now()->toDateString(),
            'transaction_type' => 'direct_cost',
            'category' => 'supplies',
            'amount' => 1000000,
        ];

        $response = $this->postJson('/api/v1/cost-center-management/transactions', $data);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Cost center tidak aktif dan tidak dapat digunakan',
            ]);
    }

    /** @test */
    public function it_validates_required_fields_for_transaction()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/v1/cost-center-management/transactions', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cost_center_id', 'transaction_date', 'transaction_type', 'category', 'amount']);
    }

    /** @test */
    public function it_can_set_budget_via_api()
    {
        $this->actingAs($this->user);

        $orgUnit = MdmOrganizationUnit::factory()->create();
        $costCenter = CostCenter::factory()->create([
            'organization_unit_id' => $orgUnit->id,
        ]);

        $data = [
            'cost_center_id' => $costCenter->id,
            'fiscal_year' => 2026,
            'period_month' => 1,
            'budgets' => [
                ['category' => 'personnel', 'amount' => 5000000],
                ['category' => 'supplies', 'amount' => 2000000],
            ],
        ];

        $response = $this->postJson('/api/v1/cost-center-management/budgets', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('cost_center_budgets', [
            'cost_center_id' => $costCenter->id,
            'fiscal_year' => 2026,
            'period_month' => 1,
            'category' => 'personnel',
            'budget_amount' => 5000000,
        ]);
    }

    /** @test */
    public function it_validates_required_fields_for_budget()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/v1/cost-center-management/budgets', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cost_center_id', 'fiscal_year', 'period_month', 'budgets']);
    }

    /** @test */
    public function it_can_get_service_line_cost_analysis()
    {
        $this->actingAs($this->user);

        $orgUnit = MdmOrganizationUnit::factory()->create();
        $costCenter = CostCenter::factory()->create([
            'organization_unit_id' => $orgUnit->id,
        ]);

        $serviceLine = ServiceLine::factory()->create();
        ServiceLineMember::factory()->create([
            'service_line_id' => $serviceLine->id,
            'cost_center_id' => $costCenter->id,
            'allocation_percentage' => 100,
        ]);

        CostCenterTransaction::factory()->create([
            'cost_center_id' => $costCenter->id,
            'transaction_type' => 'direct_cost',
            'amount' => 2000000,
            'transaction_date' => Carbon::now(),
        ]);

        $response = $this->getJson("/api/v1/cost-center-management/service-lines/{$serviceLine->id}/cost-analysis?" . http_build_query([
            'period_start' => Carbon::now()->startOfMonth()->toDateString(),
            'period_end' => Carbon::now()->endOfMonth()->toDateString(),
        ]));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'service_line_id' => $serviceLine->id,
                    'total_cost' => 2000000,
                ],
            ]);
    }
}
