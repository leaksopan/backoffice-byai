<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MasterDataManagementModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Insert or update module
        $module = DB::table('modules')
            ->where('key', 'master-data-management')
            ->first();

        if ($module) {
            $moduleId = $module->id;
            DB::table('modules')
                ->where('id', $moduleId)
                ->update([
                    'name' => 'Master Data Management',
                    'description' => 'Pengelolaan data referensi untuk seluruh sistem ERP BLUD',
                    'icon' => 'heroicon-o-database',
                    'entry_route' => 'mdm.dashboard',
                    'sort' => 10,
                    'is_active' => true,
                    'updated_at' => now(),
                ]);
        } else {
            $moduleId = DB::table('modules')->insertGetId([
                'key' => 'master-data-management',
                'name' => 'Master Data Management',
                'description' => 'Pengelolaan data referensi untuk seluruh sistem ERP BLUD',
                'icon' => 'heroicon-o-database',
                'entry_route' => 'mdm.dashboard',
                'sort' => 10,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create permissions
        $permissions = [
            'access master-data-management',
            'master-data-management.view',
            'master-data-management.create',
            'master-data-management.edit',
            'master-data-management.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign all permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }

        // Delete old menu entries for this module to avoid duplicates
        DB::table('module_menus')->where('module_id', $moduleId)->delete();

        // Create menu entries
        $menus = [
            [
                'module_id' => $moduleId,
                'label' => 'Dashboard',
                'route_name' => 'mdm.dashboard',
                'icon' => 'heroicon-o-home',
                'sort' => 1,
                'group' => null,
                'permission_name' => null,
                'is_active' => true,
            ],
            [
                'module_id' => $moduleId,
                'label' => 'Struktur Organisasi',
                'route_name' => 'mdm.organization-units.index',
                'icon' => 'heroicon-o-building-office',
                'sort' => 2,
                'group' => 'Master Data',
                'permission_name' => 'master-data-management.view',
                'is_active' => true,
            ],
            [
                'module_id' => $moduleId,
                'label' => 'Chart of Accounts',
                'route_name' => 'mdm.coa.index',
                'icon' => 'heroicon-o-calculator',
                'sort' => 3,
                'group' => 'Master Data',
                'permission_name' => 'master-data-management.view',
                'is_active' => true,
            ],
            [
                'module_id' => $moduleId,
                'label' => 'Sumber Dana',
                'route_name' => 'mdm.funding-sources.index',
                'icon' => 'heroicon-o-banknotes',
                'sort' => 4,
                'group' => 'Master Data',
                'permission_name' => 'master-data-management.view',
                'is_active' => true,
            ],
            [
                'module_id' => $moduleId,
                'label' => 'Katalog Layanan',
                'route_name' => 'mdm.services.index',
                'icon' => 'heroicon-o-clipboard-document-list',
                'sort' => 5,
                'group' => 'Master Data',
                'permission_name' => 'master-data-management.view',
                'is_active' => true,
            ],
            [
                'module_id' => $moduleId,
                'label' => 'Tarif Layanan',
                'route_name' => 'mdm.tariffs.index',
                'icon' => 'heroicon-o-currency-dollar',
                'sort' => 6,
                'group' => 'Master Data',
                'permission_name' => 'master-data-management.view',
                'is_active' => true,
            ],
            [
                'module_id' => $moduleId,
                'label' => 'SDM',
                'route_name' => 'mdm.human-resources.index',
                'icon' => 'heroicon-o-users',
                'sort' => 7,
                'group' => 'Master Data',
                'permission_name' => 'master-data-management.view',
                'is_active' => true,
            ],
            [
                'module_id' => $moduleId,
                'label' => 'Aset',
                'route_name' => 'mdm.assets.index',
                'icon' => 'heroicon-o-cube',
                'sort' => 8,
                'group' => 'Master Data',
                'permission_name' => 'master-data-management.view',
                'is_active' => true,
            ],
        ];

        foreach ($menus as $menu) {
            DB::table('module_menus')->insert(array_merge($menu, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
