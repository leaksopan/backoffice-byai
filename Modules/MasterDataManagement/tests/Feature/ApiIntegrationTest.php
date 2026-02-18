<?php

namespace Modules\MasterDataManagement\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterDataManagement\app\Models\MdmOrganizationUnit;
use Modules\MasterDataManagement\app\Models\MdmChartOfAccount;
use Modules\MasterDataManagement\app\Models\MdmFundingSource;
use Modules\MasterDataManagement\app\Models\MdmServiceCatalog;
use Modules\MasterDataManagement\app\Models\MdmTariff;
use Modules\MasterDataManagement\app\Models\MdmHumanResource;
use Modules\MasterDataManagement\app\Models\MdmAsset;
use App\Models\User;
use Carbon\Carbon;

/**
 * @test Feature: master-data-management, Integration Tests
 * Test API endpoints untuk integrasi dengan modul lain
 */
class ApiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('access master-data-management');
        $this->actingAs($this->user);
    }

    /** @test */
    public function api_returns_organization_units_list()
    {
        $units = MdmOrganizationUnit::factory()->count(5)->create();

        $response = $this->getJson('/api/mdm/organization-units');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'code', 'name', 'type', 'is_active']
                ]
            ]);
    }

    /** @test */
    public function api_returns_single_organization_unit()
    {
        $unit = MdmOrganizationUnit::factory()->create();

        $response = $this->getJson("/api/mdm/organization-units/{$unit->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $unit->id,
                    'code' => $unit->code,
                    'name' => $unit->name,
                ]
            ]);
    }

    /** @test */
    public function api_returns_organization_unit_descendants()
    {
        $parent = MdmOrganizationUnit::factory()->create();
        $child1 = MdmOrganizationUnit::factory()->create(['parent_id' => $parent->id]);
        $child2 = MdmOrganizationUnit::factory()->create(['parent_id' => $parent->id]);
        $grandchild = MdmOrganizationUnit::factory()->create(['parent_id' => $child1->id]);

        $response = $this->getJson("/api/mdm/organization-units/{$parent->id}/descendants");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function api_returns_chart_of_accounts_list()
    {
        $accounts = MdmChartOfAccount::factory()->count(5)->create();

        $response = $this->getJson('/api/mdm/chart-of-accounts');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    /** @test */
    public function api_filters_chart_of_accounts_by_category()
    {
        MdmChartOfAccount::factory()->create(['category' => 'asset']);
        MdmChartOfAccount::factory()->create(['category' => 'asset']);
        MdmChartOfAccount::factory()->create(['category' => 'expense']);

        $response = $this->getJson('/api/mdm/chart-of-accounts/by-category/asset');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function api_returns_only_postable_accounts()
    {
        MdmChartOfAccount::factory()->create(['is_header' => false]);
        MdmChartOfAccount::factory()->create(['is_header' => false]);
        MdmChartOfAccount::factory()->create(['is_header' => true]);

        $response = $this->getJson('/api/mdm/chart-of-accounts/postable');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function api_returns_funding_sources_list()
    {
        $sources = MdmFundingSource::factory()->count(5)->create();

        $response = $this->getJson('/api/mdm/funding-sources');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    /** @test */
    public function api_filters_funding_sources_active_on_date()
    {
        $date = Carbon::parse('2026-06-15');
        
        MdmFundingSource::factory()->create([
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_active' => true
        ]);
        
        MdmFundingSource::factory()->create([
            'start_date' => '2027-01-01',
            'end_date' => '2027-12-31',
            'is_active' => true
        ]);

        $response = $this->getJson("/api/mdm/funding-sources/active-on/{$date->format('Y-m-d')}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function api_returns_services_list()
    {
        $unit = MdmOrganizationUnit::factory()->create();
        $services = MdmServiceCatalog::factory()->count(5)->create(['unit_id' => $unit->id]);

        $response = $this->getJson('/api/mdm/services');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    /** @test */
    public function api_filters_services_by_category()
    {
        $unit = MdmOrganizationUnit::factory()->create();
        
        MdmServiceCatalog::factory()->create(['unit_id' => $unit->id, 'category' => 'rawat_jalan']);
        MdmServiceCatalog::factory()->create(['unit_id' => $unit->id, 'category' => 'rawat_jalan']);
        MdmServiceCatalog::factory()->create(['unit_id' => $unit->id, 'category' => 'igd']);

        $response = $this->getJson('/api/mdm/services/by-category/rawat_jalan');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function api_filters_services_by_unit()
    {
        $unit1 = MdmOrganizationUnit::factory()->create();
        $unit2 = MdmOrganizationUnit::factory()->create();
        
        MdmServiceCatalog::factory()->count(3)->create(['unit_id' => $unit1->id]);
        MdmServiceCatalog::factory()->count(2)->create(['unit_id' => $unit2->id]);

        $response = $this->getJson("/api/mdm/services/by-unit/{$unit1->id}");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function api_returns_applicable_tariff()
    {
        $unit = MdmOrganizationUnit::factory()->create();
        $service = MdmServiceCatalog::factory()->create(['unit_id' => $unit->id]);
        
        $tariff = MdmTariff::factory()->create([
            'service_id' => $service->id,
            'service_class' => 'kelas_1',
            'payer_type' => 'umum',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_active' => true
        ]);

        $response = $this->getJson("/api/mdm/tariffs/applicable?service_id={$service->id}&class=kelas_1&payer_type=umum&date=2026-06-15");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $tariff->id,
                    'tariff_amount' => (string) $tariff->tariff_amount
                ]
            ]);
    }

    /** @test */
    public function api_returns_tariff_breakdown()
    {
        $unit = MdmOrganizationUnit::factory()->create();
        $service = MdmServiceCatalog::factory()->create(['unit_id' => $unit->id]);
        $tariff = MdmTariff::factory()->create(['service_id' => $service->id]);

        $response = $this->getJson("/api/mdm/tariffs/{$tariff->id}/breakdown");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'tariff_id',
                    'total_amount',
                    'breakdowns'
                ]
            ]);
    }

    /** @test */
    public function api_returns_human_resources_list()
    {
        $hrs = MdmHumanResource::factory()->count(5)->create();

        $response = $this->getJson('/api/mdm/human-resources');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    /** @test */
    public function api_filters_human_resources_by_unit()
    {
        $unit1 = MdmOrganizationUnit::factory()->create();
        $unit2 = MdmOrganizationUnit::factory()->create();
        
        $hr1 = MdmHumanResource::factory()->create();
        $hr2 = MdmHumanResource::factory()->create();
        $hr3 = MdmHumanResource::factory()->create();
        
        $hr1->assignments()->create([
            'unit_id' => $unit1->id,
            'allocation_percentage' => 100,
            'start_date' => now(),
            'is_active' => true
        ]);
        
        $hr2->assignments()->create([
            'unit_id' => $unit1->id,
            'allocation_percentage' => 50,
            'start_date' => now(),
            'is_active' => true
        ]);
        
        $hr3->assignments()->create([
            'unit_id' => $unit2->id,
            'allocation_percentage' => 100,
            'start_date' => now(),
            'is_active' => true
        ]);

        $response = $this->getJson("/api/mdm/human-resources/by-unit/{$unit1->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function api_returns_hr_assignments()
    {
        $unit = MdmOrganizationUnit::factory()->create();
        $hr = MdmHumanResource::factory()->create();
        
        $hr->assignments()->create([
            'unit_id' => $unit->id,
            'allocation_percentage' => 100,
            'start_date' => now(),
            'is_active' => true
        ]);

        $response = $this->getJson("/api/mdm/human-resources/{$hr->id}/assignments");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function api_returns_assets_list()
    {
        $assets = MdmAsset::factory()->count(5)->create();

        $response = $this->getJson('/api/mdm/assets');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    /** @test */
    public function api_filters_assets_by_location()
    {
        $unit1 = MdmOrganizationUnit::factory()->create();
        $unit2 = MdmOrganizationUnit::factory()->create();
        
        MdmAsset::factory()->count(3)->create(['current_location_id' => $unit1->id]);
        MdmAsset::factory()->count(2)->create(['current_location_id' => $unit2->id]);

        $response = $this->getJson("/api/mdm/assets/by-location/{$unit1->id}");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function api_returns_asset_depreciation_schedule()
    {
        $asset = MdmAsset::factory()->create([
            'acquisition_value' => 100000,
            'useful_life_years' => 5,
            'depreciation_method' => 'straight_line',
            'residual_value' => 10000
        ]);

        $response = $this->getJson("/api/mdm/assets/{$asset->id}/depreciation-schedule");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'asset_id',
                    'monthly_depreciation',
                    'accumulated_depreciation',
                    'book_value'
                ]
            ]);
    }
}
