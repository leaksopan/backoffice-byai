<?php

namespace Modules\MasterDataManagement\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class OrganizationUnitResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin role and user
        $adminRole = Role::create(['name' => 'admin']);
        
        // Create necessary permissions
        Permission::create(['name' => 'access master-data-management']);
        Permission::create(['name' => 'master-data-management.view']);
        Permission::create(['name' => 'master-data-management.create']);
        Permission::create(['name' => 'master-data-management.edit']);
        Permission::create(['name' => 'master-data-management.delete']);
        
        $adminRole->givePermissionTo([
            'access master-data-management',
            'master-data-management.view',
            'master-data-management.create',
            'master-data-management.edit',
            'master-data-management.delete',
        ]);

        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
        ]);
        
        $this->adminUser->assignRole('admin');
    }

    /** @test */
    public function it_can_access_organization_unit_list_page()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/organization-units');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_access_organization_unit_create_page()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/organization-units/create');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_view_organization_unit()
    {
        $unit = MdmOrganizationUnit::create([
            'code' => 'ORG001',
            'name' => 'Test Unit',
            'type' => 'installation',
            'level' => 0,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get("/admin/organization-units/{$unit->id}");

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_access_organization_unit_edit_page()
    {
        $unit = MdmOrganizationUnit::create([
            'code' => 'ORG001',
            'name' => 'Test Unit',
            'type' => 'installation',
            'level' => 0,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get("/admin/organization-units/{$unit->id}/edit");

        $response->assertStatus(200);
    }

    /** @test */
    public function it_displays_hierarchy_in_parent_selector()
    {
        // Create parent unit
        $parent = MdmOrganizationUnit::create([
            'code' => 'PARENT',
            'name' => 'Parent Unit',
            'type' => 'installation',
            'level' => 0,
            'hierarchy_path' => '/PARENT',
            'is_active' => true,
        ]);

        // Create child unit
        $child = MdmOrganizationUnit::create([
            'code' => 'CHILD',
            'name' => 'Child Unit',
            'type' => 'department',
            'parent_id' => $parent->id,
            'level' => 1,
            'hierarchy_path' => '/PARENT/CHILD',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/organization-units/create');

        $response->assertStatus(200);
        // Verify that parent options are available
        $response->assertSee('Parent Unit');
    }

    /** @test */
    public function it_shows_hierarchy_path_in_table()
    {
        $unit = MdmOrganizationUnit::create([
            'code' => 'ORG001',
            'name' => 'Test Unit',
            'type' => 'installation',
            'level' => 0,
            'hierarchy_path' => '/ORG001',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/organization-units');

        $response->assertStatus(200);
        $response->assertSee('Test Unit');
    }
}

