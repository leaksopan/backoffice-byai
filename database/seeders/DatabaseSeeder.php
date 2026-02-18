<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\ModuleForm;
use App\Models\ModuleMenu;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeds Modulify core RBAC, module registry, menus, and dynamic form defaults.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Core module registry used by dashboard, module entry routing, and RBAC seeding.
        $moduleDefinitions = [
            'admin-center' => [
                'name' => 'Admin Center',
                'description' => 'User, Role & Permission management',
                'icon' => '🛠',
                'entry_route' => 'ac.dashboard',
                'sort_order' => 0,
                'is_active' => true,
            ],
            'example-modules' => [
                'name' => 'Example Modules',
                'description' => 'In-app developer guide for building new modules.',
                'icon' => 'heroicon-o-squares-2x2',
                'entry_route' => 'example.dashboard',
                'sort_order' => 2,
                'is_active' => true,
            ],
            'settings' => [
                'name' => 'Settings',
                'description' => 'Manage global branding and application options.',
                'icon' => 'heroicon-o-cog-6-tooth',
                'entry_route' => 'settings.dashboard',
                'sort_order' => 1,
                'is_active' => true,
            ],
            'strategic-management' => [
                'name' => 'Strategic Management',
                'description' => 'Visi, Misi, KPI, Roadmap & Evaluasi Kinerja BLUD',
                'icon' => 'heroicon-o-light-bulb',
                'entry_route' => 'sm.dashboard',
                'sort' => 2,
                'is_active' => true,
            ],
            'master-data-management' => [
                'name' => 'Master Data Management',
                'description' => 'Pengelolaan data referensi untuk seluruh sistem ERP BLUD',
                'icon' => 'heroicon-o-database',
                'entry_route' => 'mdm.dashboard',
                'sort' => 3,
                'is_active' => true,
            ],
            'cost-center-management' => [
                'name' => 'Cost Center Management',
                'description' => 'Manajemen Cost Center, Alokasi Biaya, dan Pelaporan',
                'icon' => 'heroicon-o-building-office-2',
                'entry_route' => 'ccm.dashboard.index',
                'sort' => 4,
                'is_active' => true,
            ],
        ];

        $modulePermissions = collect($moduleDefinitions)
            ->keys()
            ->flatMap(function (string $moduleKey) {
                return [
                    'access '.$moduleKey,
                    $moduleKey.'.view',
                    $moduleKey.'.create',
                    $moduleKey.'.edit',
                    $moduleKey.'.delete',
                ];
            })
            ->values()
            ->all();

        $globalPermissions = [
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            'permissions.view',
            'permissions.create',
            'permissions.edit',
            'permissions.delete',
            'assignments.manage',
            'modules.manage',
        ];

        $allPermissionNames = array_values(array_unique(array_merge($modulePermissions, $globalPermissions)));

        foreach ($allPermissionNames as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $removedModuleKeys = ['project-management', 'inventory'];
        $removedPermissionNames = collect($removedModuleKeys)
            ->flatMap(function (string $moduleKey) {
                return [
                    'access '.$moduleKey,
                    $moduleKey.'.view',
                    $moduleKey.'.create',
                    $moduleKey.'.edit',
                    $moduleKey.'.delete',
                ];
            })
            ->values()
            ->all();

        $removedModuleIds = Module::query()
            ->whereIn('key', $removedModuleKeys)
            ->pluck('id');

        if ($removedModuleIds->isNotEmpty()) {
            ModuleForm::query()->whereIn('module_id', $removedModuleIds)->delete();
        }

        ModuleMenu::query()->whereIn('module_key', $removedModuleKeys)->delete();
        Module::query()->whereIn('key', $removedModuleKeys)->delete();
        Permission::query()->whereIn('name', $removedPermissionNames)->delete();

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);

        $allPermissionNames = Permission::query()->orderBy('name')->pluck('name')->all();
        $superAdminRole->syncPermissions($allPermissionNames);

        $adminPermissionNames = array_values(array_filter($allPermissionNames, function (string $permission) {
            return ! str_ends_with($permission, '.delete');
        }));
        $adminRole->syncPermissions($adminPermissionNames);

        $userRole->syncPermissions([
            'access example-modules',
            'example-modules.view',
            'access strategic-management',
            'strategic-management.view',
            'access master-data-management',
            'master-data-management.view',
        ]);

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@company.test'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );
        $adminUser->syncRoles([$adminRole, $superAdminRole]);

        $modules = collect();

        foreach ($moduleDefinitions as $moduleKey => $definition) {
            $modules->put($moduleKey, Module::updateOrCreate(
                ['key' => $moduleKey],
                array_merge($definition, ['key' => $moduleKey])
            ));
        }

        $legacyModule = Module::query()->where('key', 'master-data')->first();
        if ($legacyModule) {
            ModuleMenu::query()->where('module_id', $legacyModule->id)->delete();
            $legacyModule->delete();
        }
        $menusByModule = [
            'admin-center' => [
                [
                    'label' => 'Dashboard',
                    'route_name' => 'ac.dashboard',
                    'icon' => 'heroicon-o-home',
                    'url' => null,
                    'sort_order' => 1,
                    'permission_name' => 'admin-center.view',
                    'section' => 'MAIN',
                    'is_active' => true,
                ],
                [
                    'label' => 'Users',
                    'route_name' => 'ac.users.index',
                    'icon' => 'heroicon-o-users',
                    'url' => null,
                    'sort_order' => 2,
                    'permission_name' => 'users.view',
                    'section' => 'MAIN',
                    'is_active' => true,
                ],
                [
                    'label' => 'Roles',
                    'route_name' => 'ac.roles.index',
                    'icon' => 'heroicon-o-shield-check',
                    'url' => null,
                    'sort_order' => 3,
                    'permission_name' => 'roles.view',
                    'section' => 'MAIN',
                    'is_active' => true,
                ],
                [
                    'label' => 'Permissions',
                    'route_name' => 'ac.permissions.index',
                    'icon' => 'heroicon-o-key',
                    'url' => null,
                    'sort_order' => 4,
                    'permission_name' => 'permissions.view',
                    'section' => 'MAIN',
                    'is_active' => true,
                ],
                [
                    'label' => 'Modules Management',
                    'route_name' => 'ac.modules.index',
                    'icon' => 'heroicon-o-squares-2x2',
                    'url' => null,
                    'sort_order' => 5,
                    'permission_name' => 'modules.manage',
                    'section' => 'ADMIN',
                    'is_active' => true,
                ],
                [
                    'label' => 'Assign Roles to User',
                    'route_name' => 'ac.assign.user-roles',
                    'icon' => 'heroicon-o-user-plus',
                    'url' => null,
                    'sort_order' => 1,
                    'permission_name' => 'assignments.manage',
                    'section' => 'ADMIN',
                    'is_active' => true,
                ],
                [
                    'label' => 'Assign Permissions to Role',
                    'route_name' => 'ac.assign.role-permissions',
                    'icon' => 'heroicon-o-rectangle-group',
                    'url' => null,
                    'sort_order' => 2,
                    'permission_name' => 'assignments.manage',
                    'section' => 'ADMIN',
                    'is_active' => true,
                ],
                [
                    'label' => 'Module Access Matrix',
                    'route_name' => 'ac.assign.module-access',
                    'icon' => 'heroicon-o-table-cells',
                    'url' => null,
                    'sort_order' => 3,
                    'permission_name' => 'assignments.manage',
                    'section' => 'ADMIN',
                    'is_active' => true,
                ],
            ],
            'settings' => [
                [
                    'label' => 'Dashboard',
                    'route_name' => 'settings.dashboard',
                    'icon' => 'heroicon-o-home',
                    'url' => null,
                    'sort_order' => 1,
                    'permission_name' => 'settings.view',
                    'section' => 'MAIN',
                    'is_active' => true,
                ],
                [
                    'label' => 'Branding',
                    'route_name' => 'settings.branding',
                    'icon' => 'heroicon-o-cog-6-tooth',
                    'url' => null,
                    'sort_order' => 2,
                    'permission_name' => 'settings.edit',
                    'section' => 'ADMIN',
                    'is_active' => true,
                ],
            ],
            'example-modules' => [
                [
                    'label' => 'Dashboard',
                    'route_name' => 'example.dashboard',
                    'icon' => 'heroicon-o-home',
                    'url' => null,
                    'sort_order' => 1,
                    'permission_name' => 'example-modules.view',
                    'section' => 'MAIN',
                    'is_active' => true,
                ],
                [
                    'label' => 'File Structure',
                    'route_name' => 'example.files',
                    'icon' => 'heroicon-o-clipboard-document',
                    'url' => null,
                    'sort_order' => 2,
                    'permission_name' => 'example-modules.view',
                    'section' => 'MAIN',
                    'is_active' => true,
                ],
                [
                    'label' => 'Sidebar Config',
                    'route_name' => 'example.sidebar',
                    'icon' => 'heroicon-o-table-cells',
                    'url' => null,
                    'sort_order' => 3,
                    'permission_name' => 'example-modules.view',
                    'section' => 'MAIN',
                    'is_active' => true,
                ],
            ],
            'strategic-management' => [
                [
                    'label' => 'Dashboard',
                    'route_name' => 'sm.dashboard',
                    'icon' => 'heroicon-o-home',
                    'sort_order' => 1,
                    'permission_name' => 'strategic-management.view',
                    'section' => 'MAIN',
                    'is_active' => true,
                ],
                [
                    'label' => 'Visi & Misi',
                    'route_name' => 'sm.visions.index',
                    'icon' => 'heroicon-o-eye',
                    'sort_order' => 2,
                    'permission_name' => 'strategic-management.view',
                    'section' => 'MAIN',
                    'is_active' => true,
                ],
                [
                    'label' => 'KPI',
                    'route_name' => 'sm.kpis.index',
                    'icon' => 'heroicon-o-chart-bar',
                    'sort_order' => 3,
                    'permission_name' => 'strategic-management.view',
                    'section' => 'MAIN',
                    'is_active' => true,
                ],
                [
                    'label' => 'Roadmap',
                    'route_name' => 'sm.roadmap.index',
                    'icon' => 'heroicon-o-map',
                    'sort_order' => 4,
                    'permission_name' => 'strategic-management.view',
                    'section' => 'MAIN',
                    'is_active' => true,
                ],
                [
                    'label' => 'Evaluasi',
                    'route_name' => 'sm.evaluations.index',
                    'icon' => 'heroicon-o-clipboard-document-check',
                    'sort_order' => 5,
                    'permission_name' => 'strategic-management.view',
                    'section' => 'MAIN',
                    'is_active' => true,
                ],
                [
                    'label' => 'Settings',
                    'route_name' => 'sm.settings',
                    'icon' => 'heroicon-o-cog-6-tooth',
                    'sort_order' => 1,
                    'permission_name' => 'strategic-management.edit',
                    'section' => 'ADMIN',
                    'is_active' => true,
                ],
            ],
            'master-data-management' => [
                [
                    'label' => 'Dashboard',
                    'route_name' => 'mdm.dashboard',
                    'icon' => 'heroicon-o-home',
                    'sort_order' => 1,
                    'permission_name' => 'master-data-management.view',
                    'section' => 'MAIN',
                    'is_active' => true,
                ],
                [
                    'label' => 'Struktur Organisasi',
                    'route_name' => 'mdm.organization-units.index',
                    'icon' => 'heroicon-o-building-office',
                    'sort_order' => 2,
                    'permission_name' => 'master-data-management.view',
                    'section' => 'MAIN',
                    'is_active' => true,
                ],
                [
                    'label' => 'Chart of Accounts',
                    'route_name' => 'mdm.coa.index',
                    'icon' => 'heroicon-o-calculator',
                    'sort_order' => 3,
                    'permission_name' => 'master-data-management.view',
                    'section' => 'MAIN',
                    'is_active' => true,
                ],
                [
                    'label' => 'Sumber Dana',
                    'route_name' => 'mdm.funding-sources.index',
                    'icon' => 'heroicon-o-banknotes',
                    'sort_order' => 4,
                    'permission_name' => 'master-data-management.view',
                    'section' => 'MAIN',
                    'is_active' => true,
                ],
                [
                    'label' => 'Katalog Layanan',
                    'route_name' => 'mdm.services.index',
                    'icon' => 'heroicon-o-clipboard-document-list',
                    'sort_order' => 5,
                    'permission_name' => 'master-data-management.view',
                    'section' => 'MAIN',
                    'is_active' => true,
                ],
                [
                    'label' => 'Tarif Layanan',
                    'route_name' => 'mdm.tariffs.index',
                    'icon' => 'heroicon-o-currency-dollar',
                    'sort_order' => 6,
                    'permission_name' => 'master-data-management.view',
                    'section' => 'MAIN',
                    'is_active' => true,
                ],
                [
                    'label' => 'SDM',
                    'route_name' => 'mdm.human-resources.index',
                    'icon' => 'heroicon-o-users',
                    'sort_order' => 7,
                    'permission_name' => 'master-data-management.view',
                    'section' => 'MAIN',
                    'is_active' => true,
                ],
                [
                    'label' => 'Aset',
                    'route_name' => 'mdm.assets.index',
                    'icon' => 'heroicon-o-cube',
                    'sort_order' => 8,
                    'permission_name' => 'master-data-management.view',
                    'section' => 'MAIN',
                    'is_active' => true,
                ],
            ],
            'cost-center-management' => [
                [
                    'label' => 'Dashboard',
                    'route_name' => 'ccm.dashboard.index',
                    'icon' => 'heroicon-o-home',
                    'sort_order' => 1,
                    'permission_name' => 'cost-center-management.view',
                    'section' => 'MAIN',
                    'is_active' => true,
                ],
                [
                    'label' => 'Cost Centers',
                    'route_name' => 'ccm.cost-centers.index',
                    'icon' => 'heroicon-o-building-office-2',
                    'sort_order' => 2,
                    'permission_name' => 'cost-center-management.view',
                    'section' => 'MAIN',
                    'is_active' => true,
                ],
                [
                    'label' => 'Allocation Process',
                    'route_name' => 'ccm.allocation-process.index',
                    'icon' => 'heroicon-o-arrows-right-left',
                    'sort_order' => 3,
                    'permission_name' => 'cost-center-management.view',
                    'section' => 'MAIN',
                    'is_active' => true,
                ],
                [
                    'label' => 'Reports',
                    'route_name' => 'ccm.reports.index',
                    'icon' => 'heroicon-o-document-chart-bar',
                    'sort_order' => 4,
                    'permission_name' => 'cost-center-management.view',
                    'section' => 'MAIN',
                    'is_active' => true,
                ],
                [
                    'label' => 'Approval - Allocation Rules',
                    'route_name' => 'ccm.approval.allocation-rules',
                    'icon' => 'heroicon-o-check-circle',
                    'sort_order' => 1,
                    'permission_name' => 'cost-center-management.edit',
                    'section' => 'ADMIN',
                    'is_active' => true,
                ],
                [
                    'label' => 'Approval - Budget Revisions',
                    'route_name' => 'ccm.budget-revisions.index',
                    'icon' => 'heroicon-o-document-check',
                    'sort_order' => 2,
                    'permission_name' => 'cost-center-management.edit',
                    'section' => 'ADMIN',
                    'is_active' => true,
                ],
            ],
        ];

        foreach ($menusByModule as $moduleKey => $menus) {
            $module = $modules->get($moduleKey);

            if (! $module) {
                continue;
            }

            foreach ($menus as $menu) {
                ModuleMenu::updateOrCreate(
                    ['module_key' => $module->key, 'label' => $menu['label']],
                    array_merge($menu, ['module_key' => $module->key])
                );
            }
        }

        // Seed sample data
        $this->call([
            MasterDataSampleSeeder::class,
        ]);
    }
}
