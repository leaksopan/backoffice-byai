<?php

namespace Modules\MasterDataManagement\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Modules\MasterDataManagement\Http\Requests\StoreOrganizationUnitRequest;
use Modules\MasterDataManagement\Http\Requests\StoreChartOfAccountRequest;
use Modules\MasterDataManagement\Http\Requests\StoreFundingSourceRequest;
use App\Models\User;
use Spatie\Permission\Models\Permission;

/**
 * Property Test: Permission-Based Access Control
 * Feature: master-data-management, Property 16: Permission-Based Access Control
 * Validates: Requirements 10.2
 */
class PermissionBasedAccessControlTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Property: For any user attempting to access master data operations,
     * the system should grant access only if the user has the corresponding permission
     *
     * @test
     */
    public function property_grants_access_only_with_permission()
    {
        for ($i = 0; $i < 100; $i++) {
            $this->runPermissionCheckTest();
        }
    }

    private function runPermissionCheckTest(): void
    {
        // Create permissions
        $permissions = [
            'master-data-management.view',
            'master-data-management.create',
            'master-data-management.edit',
            'master-data-management.delete',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Test with permission
        $userWithPermission = User::factory()->create();
        $randomPermission = $permissions[array_rand($permissions)];
        $userWithPermission->givePermissionTo($randomPermission);

        $this->assertTrue(
            $userWithPermission->hasPermissionTo($randomPermission),
            "User with permission should have access"
        );

        // Test without permission
        $userWithoutPermission = User::factory()->create();
        $this->assertFalse(
            $userWithoutPermission->hasPermissionTo($randomPermission),
            "User without permission should not have access"
        );

        $userWithPermission->delete();
        $userWithoutPermission->delete();
    }

    /**
     * @test
     */
    public function property_form_request_authorize_checks_permission()
    {
        Permission::firstOrCreate(['name' => 'master-data-management.create']);

        for ($i = 0; $i < 20; $i++) {
            // User with permission
            $userWithPermission = User::factory()->create();
            $userWithPermission->givePermissionTo('master-data-management.create');

            $request = new StoreOrganizationUnitRequest();
            $request->setUserResolver(fn() => $userWithPermission);

            $this->assertTrue($request->authorize(), "User with permission should be authorized");

            // User without permission
            $userWithoutPermission = User::factory()->create();
            $request = new StoreOrganizationUnitRequest();
            $request->setUserResolver(fn() => $userWithoutPermission);

            $this->assertFalse($request->authorize(), "User without permission should not be authorized");

            $userWithPermission->delete();
            $userWithoutPermission->delete();
        }
    }
}
