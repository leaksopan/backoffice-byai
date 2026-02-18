<?php

namespace Modules\CostCenterManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use App\Models\Module;
use App\Models\ModuleMenu;

class CostCenterManagementModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'access cost-center-management',
            'cost-center-management.view',
            'cost-center-management.view-all',
            'cost-center-management.create',
            'cost-center-management.edit',
            'cost-center-management.delete',
            'cost-center-management.allocate',
            'cost-center-management.approve',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create module entry
        $module = Module::firstOrCreate(
            ['key' => 'cost-center-management'],
            [
                'name' => 'Cost Center Management',
                'description' => 'Modul pengelolaan pusat biaya dan pusat pertanggungjawaban',
                'icon' => 'heroicon-o-building-office-2',
                'entry_route' => 'ccm.dashboard',
                'is_active' => true,
                'sort_order' => 3,
            ]
        );

        // Create module menus
        $menus = [
            [
                'module_key' => 'cost-center-management',
                'label' => 'Dashboard',
                'route_name' => 'ccm.dashboard',
                'icon' => 'heroicon-o-home',
                'section' => 'MAIN',
                'sort_order' => 1,
                'is_active' => true,
                'permission_name' => null,
            ],
            [
                'module_key' => 'cost-center-management',
                'label' => 'Cost Center Dashboard',
                'route_name' => 'ccm.dashboard.index',
                'icon' => 'heroicon-o-chart-bar',
                'section' => 'ANALYTICS',
                'sort_order' => 2,
                'is_active' => true,
                'permission_name' => 'cost-center-management.view',
            ],
            [
                'module_key' => 'cost-center-management',
                'label' => 'Cost Centers',
                'route_name' => 'ccm.cost-centers.index',
                'icon' => 'heroicon-o-building-office',
                'section' => 'MASTER DATA',
                'sort_order' => 3,
                'is_active' => true,
                'permission_name' => 'cost-center-management.view',
            ],
            [
                'module_key' => 'cost-center-management',
                'label' => 'Allocation Rules',
                'route_name' => 'ccm.allocation-rules.index',
                'icon' => 'heroicon-o-arrows-right-left',
                'section' => 'MASTER DATA',
                'sort_order' => 4,
                'is_active' => true,
                'permission_name' => 'cost-center-management.view',
            ],
            [
                'module_key' => 'cost-center-management',
                'label' => 'Allocation Process',
                'route_name' => 'ccm.allocation-process.index',
                'icon' => 'heroicon-o-cog-6-tooth',
                'section' => 'OPERATIONS',
                'sort_order' => 5,
                'is_active' => true,
                'permission_name' => 'cost-center-management.allocate',
            ],
            [
                'module_key' => 'cost-center-management',
                'label' => 'Approval - Allocation Rules',
                'route_name' => 'ccm.approval.allocation-rules',
                'icon' => 'heroicon-o-check-circle',
                'section' => 'APPROVAL',
                'sort_order' => 6,
                'is_active' => true,
                'permission_name' => 'cost-center-management.approve',
            ],
            [
                'module_key' => 'cost-center-management',
                'label' => 'Approval - Budget Revisions',
                'route_name' => 'ccm.budget-revisions.index',
                'icon' => 'heroicon-o-check-circle',
                'section' => 'APPROVAL',
                'sort_order' => 7,
                'is_active' => true,
                'permission_name' => 'cost-center-management.approve',
            ],
        ];

        foreach ($menus as $menu) {
            ModuleMenu::firstOrCreate(
                [
                    'module_key' => $menu['module_key'],
                    'route_name' => $menu['route_name'],
                ],
                $menu
            );
        }

        $this->command->info('Cost Center Management module seeded successfully!');
    }
}

