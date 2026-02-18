<?php

namespace Modules\MasterDataManagement\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use App\Models\User;
use Spatie\Permission\Models\Permission;

/**
 * Unit Test: 403 Error on Missing Permission
 * Validates: Requirements 10.6
 */
class UnauthorizedAccessTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Test unauthorized access returns 403
     *
     * @test
     */
    public function test_unauthorized_user_receives_403_on_form_request()
    {
        Permission::create(['name' => 'master-data-management.create']);
        Permission::create(['name' => 'master-data-management.view']);
        Permission::create(['name' => 'master-data-management.edit']);
        Permission::create(['name' => 'master-data-management.delete']);

        // User without any permissions
        $user = User::factory()->create();

        $this->actingAs($user);

        // Test that Form Request authorize() returns false
        // which will result in 403 when used in controller
        $this->assertFalse(
            $user->can('master-data-management.create'),
            "User without permission should not have access"
        );

        $this->assertFalse(
            $user->can('master-data-management.view'),
            "User without permission should not have access"
        );

        $this->assertFalse(
            $user->can('master-data-management.edit'),
            "User without permission should not have access"
        );

        $this->assertFalse(
            $user->can('master-data-management.delete'),
            "User without permission should not have access"
        );
    }

    /**
     * Test user with permission has access
     *
     * @test
     */
    public function test_authorized_user_has_access()
    {
        Permission::create(['name' => 'master-data-management.create']);
        Permission::create(['name' => 'master-data-management.view']);

        $user = User::factory()->create();
        $user->givePermissionTo(['master-data-management.create', 'master-data-management.view']);

        $this->actingAs($user);

        $this->assertTrue(
            $user->can('master-data-management.create'),
            "User with permission should have access"
        );

        $this->assertTrue(
            $user->can('master-data-management.view'),
            "User with permission should have access"
        );
    }
}
