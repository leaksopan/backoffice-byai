<?php

namespace Modules\MasterDataManagement\Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use Modules\MasterDataManagement\Models\MdmChartOfAccount;
use Modules\MasterDataManagement\Models\MdmFundingSource;
use Modules\MasterDataManagement\Models\MdmServiceCatalog;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /** @test */
    public function dashboard_displays_summary_statistics()
    {
        $user = User::factory()->create();

        // Create sample data
        MdmOrganizationUnit::create([
            'code' => 'ORG001',
            'name' => 'Test Unit',
            'type' => 'installation',
            'level' => 0,
            'is_active' => true,
        ]);

        MdmChartOfAccount::create([
            'code' => '1-01-01-01-001',
            'name' => 'Test Account',
            'category' => 'asset',
            'normal_balance' => 'debit',
            'level' => 1,
            'is_header' => false,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('mdm.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('statistics');
        $response->assertViewHas('recentChanges');
        $response->assertViewHas('dataQuality');
    }

    /** @test */
    public function dashboard_shows_correct_organization_unit_count()
    {
        $user = User::factory()->create();

        MdmOrganizationUnit::create([
            'code' => 'ORG001',
            'name' => 'Active Unit',
            'type' => 'installation',
            'level' => 0,
            'is_active' => true,
        ]);

        MdmOrganizationUnit::create([
            'code' => 'ORG002',
            'name' => 'Inactive Unit',
            'type' => 'department',
            'level' => 0,
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->get(route('mdm.dashboard'));

        $response->assertStatus(200);
        
        $statistics = $response->viewData('statistics');
        $this->assertEquals(2, $statistics['organization_units']['total']);
        $this->assertEquals(1, $statistics['organization_units']['active']);
        $this->assertEquals(1, $statistics['organization_units']['inactive']);
    }

    /** @test */
    public function dashboard_calculates_data_quality_metrics()
    {
        $user = User::factory()->create();

        // Create complete record
        MdmOrganizationUnit::create([
            'code' => 'ORG001',
            'name' => 'Complete Unit',
            'type' => 'installation',
            'level' => 0,
            'is_active' => true,
            'description' => 'Complete description',
        ]);

        // Create incomplete record
        MdmOrganizationUnit::create([
            'code' => 'ORG002',
            'name' => 'Incomplete Unit',
            'type' => 'department',
            'level' => 0,
            'is_active' => true,
            'description' => null,
        ]);

        $response = $this->actingAs($user)->get(route('mdm.dashboard'));

        $response->assertStatus(200);
        
        $dataQuality = $response->viewData('dataQuality');
        $this->assertArrayHasKey('organization_units', $dataQuality);
        $this->assertArrayHasKey('completeness', $dataQuality['organization_units']);
        $this->assertEquals(1, $dataQuality['organization_units']['missing_description']);
    }
}
