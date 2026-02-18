<?php

namespace Modules\MasterDataManagement\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterDataManagement\app\Models\MdmOrganizationUnit;
use Modules\MasterDataManagement\app\Models\MdmServiceCatalog;
use Modules\MasterDataManagement\app\Models\MdmTariff;
use Modules\MasterDataManagement\app\Models\MdmHumanResource;
use Modules\MasterDataManagement\app\Models\MdmHrAssignment;
use Modules\MasterDataManagement\app\Models\MdmAsset;
use Modules\MasterDataManagement\app\Models\MdmAssetMovement;
use App\Models\User;

/**
 * @test Feature: master-data-management, Cross-Entity Relationship Tests
 * Test relationships antar entities dalam master data
 */
class CrossEntityRelationshipTest extends TestCase
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
    public function service_catalog_belongs_to_organization_unit()
    {
        $unit = MdmOrganizationUnit::factory()->create();
        $service = MdmServiceCatalog::factory()->create(['unit_id' => $unit->id]);

        $this->assertInstanceOf(MdmOrganizationUnit::class, $service->unit);
        $this->assertEquals($unit->id, $service->unit->id);
    }

    /** @test */
    public function organization_unit_has_many_services()
    {
        $unit = MdmOrganizationUnit::factory()->create();
        $services = MdmServiceCatalog::factory()->count(3)->create(['unit_id' => $unit->id]);

        $this->assertCount(3, $unit->services);
        $this->assertInstanceOf(MdmServiceCatalog::class, $unit->services->first());
    }

    /** @test */
    public function tariff_belongs_to_service_catalog()
    {
        $unit = MdmOrganizationUnit::factory()->create();
        $service = MdmServiceCatalog::factory()->create(['unit_id' => $unit->id]);
        $tariff = MdmTariff::factory()->create(['service_id' => $service->id]);

        $this->assertInstanceOf(MdmServiceCatalog::class, $tariff->service);
        $this->assertEquals($service->id, $tariff->service->id);
    }

    /** @test */
    public function service_catalog_has_many_tariffs()
    {
        $unit = MdmOrganizationUnit::factory()->create();
        $service = MdmServiceCatalog::factory()->create(['unit_id' => $unit->id]);
        
        $tariffs = MdmTariff::factory()->count(3)->create([
            'service_id' => $service->id,
            'service_class' => 'kelas_1',
            'start_date' => now()->subDays(30),
            'end_date' => now()->addDays(30)
        ]);

        $this->assertCount(3, $service->tariffs);
        $this->assertInstanceOf(MdmTariff::class, $service->tariffs->first());
    }

    /** @test */
    public function hr_assignment_belongs_to_human_resource()
    {
        $unit = MdmOrganizationUnit::factory()->create();
        $hr = MdmHumanResource::factory()->create();
        
        $assignment = $hr->assignments()->create([
            'unit_id' => $unit->id,
            'allocation_percentage' => 100,
            'start_date' => now(),
            'is_active' => true
        ]);

        $this->assertInstanceOf(MdmHumanResource::class, $assignment->humanResource);
        $this->assertEquals($hr->id, $assignment->humanResource->id);
    }

    /** @test */
    public function hr_assignment_belongs_to_organization_unit()
    {
        $unit = MdmOrganizationUnit::factory()->create();
        $hr = MdmHumanResource::factory()->create();
        
        $assignment = $hr->assignments()->create([
            'unit_id' => $unit->id,
            'allocation_percentage' => 100,
            'start_date' => now(),
            'is_active' => true
        ]);

        $this->assertInstanceOf(MdmOrganizationUnit::class, $assignment->unit);
        $this->assertEquals($unit->id, $assignment->unit->id);
    }

    /** @test */
    public function human_resource_has_many_assignments()
    {
        $unit1 = MdmOrganizationUnit::factory()->create();
        $unit2 = MdmOrganizationUnit::factory()->create();
        $hr = MdmHumanResource::factory()->create();
        
        $hr->assignments()->create([
            'unit_id' => $unit1->id,
            'allocation_percentage' => 50,
            'start_date' => now(),
            'is_active' => true
        ]);
        
        $hr->assignments()->create([
            'unit_id' => $unit2->id,
            'allocation_percentage' => 50,
            'start_date' => now(),
            'is_active' => true
        ]);

        $this->assertCount(2, $hr->assignments);
        $this->assertInstanceOf(MdmHrAssignment::class, $hr->assignments->first());
    }

    /** @test */
    public function organization_unit_has_many_hr_assignments()
    {
        $unit = MdmOrganizationUnit::factory()->create();
        $hr1 = MdmHumanResource::factory()->create();
        $hr2 = MdmHumanResource::factory()->create();
        
        $hr1->assignments()->create([
            'unit_id' => $unit->id,
            'allocation_percentage' => 100,
            'start_date' => now(),
            'is_active' => true
        ]);
        
        $hr2->assignments()->create([
            'unit_id' => $unit->id,
            'allocation_percentage' => 100,
            'start_date' => now(),
            'is_active' => true
        ]);

        $this->assertCount(2, $unit->hrAssignments);
    }

    /** @test */
    public function asset_belongs_to_organization_unit_as_location()
    {
        $unit = MdmOrganizationUnit::factory()->create();
        $asset = MdmAsset::factory()->create(['current_location_id' => $unit->id]);

        $this->assertInstanceOf(MdmOrganizationUnit::class, $asset->currentLocation);
        $this->assertEquals($unit->id, $asset->currentLocation->id);
    }

    /** @test */
    public function organization_unit_has_many_assets()
    {
        $unit = MdmOrganizationUnit::factory()->create();
        $assets = MdmAsset::factory()->count(3)->create(['current_location_id' => $unit->id]);

        $this->assertCount(3, $unit->assets);
        $this->assertInstanceOf(MdmAsset::class, $unit->assets->first());
    }

    /** @test */
    public function asset_movement_belongs_to_asset()
    {
        $unit1 = MdmOrganizationUnit::factory()->create();
        $unit2 = MdmOrganizationUnit::factory()->create();
        $asset = MdmAsset::factory()->create(['current_location_id' => $unit1->id]);
        
        $movement = $asset->movements()->create([
            'from_location_id' => $unit1->id,
            'to_location_id' => $unit2->id,
            'movement_date' => now(),
            'reason' => 'Transfer'
        ]);

        $this->assertInstanceOf(MdmAsset::class, $movement->asset);
        $this->assertEquals($asset->id, $movement->asset->id);
    }

    /** @test */
    public function asset_has_many_movements()
    {
        $unit1 = MdmOrganizationUnit::factory()->create();
        $unit2 = MdmOrganizationUnit::factory()->create();
        $unit3 = MdmOrganizationUnit::factory()->create();
        $asset = MdmAsset::factory()->create(['current_location_id' => $unit1->id]);
        
        $asset->movements()->create([
            'from_location_id' => $unit1->id,
            'to_location_id' => $unit2->id,
            'movement_date' => now()->subDays(2),
            'reason' => 'Transfer 1'
        ]);
        
        $asset->movements()->create([
            'from_location_id' => $unit2->id,
            'to_location_id' => $unit3->id,
            'movement_date' => now(),
            'reason' => 'Transfer 2'
        ]);

        $this->assertCount(2, $asset->movements);
        $this->assertInstanceOf(MdmAssetMovement::class, $asset->movements->first());
    }

    /** @test */
    public function organization_unit_hierarchy_relationship_works()
    {
        $parent = MdmOrganizationUnit::factory()->create();
        $child1 = MdmOrganizationUnit::factory()->create(['parent_id' => $parent->id]);
        $child2 = MdmOrganizationUnit::factory()->create(['parent_id' => $parent->id]);

        $this->assertInstanceOf(MdmOrganizationUnit::class, $child1->parent);
        $this->assertEquals($parent->id, $child1->parent->id);
        
        $this->assertCount(2, $parent->children);
        $this->assertInstanceOf(MdmOrganizationUnit::class, $parent->children->first());
    }

    /** @test */
    public function can_traverse_complex_relationships()
    {
        // Setup: Unit -> Service -> Tariff
        $unit = MdmOrganizationUnit::factory()->create();
        $service = MdmServiceCatalog::factory()->create(['unit_id' => $unit->id]);
        $tariff = MdmTariff::factory()->create(['service_id' => $service->id]);

        // Traverse dari tariff ke unit
        $this->assertEquals($unit->id, $tariff->service->unit->id);
        
        // Traverse dari unit ke tariff
        $this->assertEquals($tariff->id, $unit->services->first()->tariffs->first()->id);
    }

    /** @test */
    public function eager_loading_prevents_n_plus_one_queries()
    {
        $unit = MdmOrganizationUnit::factory()->create();
        MdmServiceCatalog::factory()->count(5)->create(['unit_id' => $unit->id]);

        // Without eager loading
        $services1 = MdmServiceCatalog::all();
        $queryCount1 = 0;
        foreach ($services1 as $service) {
            $queryCount1++;
            $service->unit; // This triggers a query for each service
        }

        // With eager loading
        $services2 = MdmServiceCatalog::with('unit')->get();
        $queryCount2 = 0;
        foreach ($services2 as $service) {
            $queryCount2++;
            $service->unit; // This doesn't trigger additional queries
        }

        $this->assertGreaterThan($queryCount2, $queryCount1);
    }

    /** @test */
    public function deleting_parent_unit_is_prevented_when_has_children()
    {
        $parent = MdmOrganizationUnit::factory()->create();
        $child = MdmOrganizationUnit::factory()->create(['parent_id' => $parent->id]);

        $this->expectException(\Exception::class);
        $parent->delete();
    }

    /** @test */
    public function deleting_unit_with_services_is_prevented()
    {
        $unit = MdmOrganizationUnit::factory()->create();
        $service = MdmServiceCatalog::factory()->create(['unit_id' => $unit->id]);

        $this->expectException(\Exception::class);
        $unit->delete();
    }

    /** @test */
    public function deleting_service_with_tariffs_is_prevented()
    {
        $unit = MdmOrganizationUnit::factory()->create();
        $service = MdmServiceCatalog::factory()->create(['unit_id' => $unit->id]);
        $tariff = MdmTariff::factory()->create(['service_id' => $service->id]);

        $this->expectException(\Exception::class);
        $service->delete();
    }
}
