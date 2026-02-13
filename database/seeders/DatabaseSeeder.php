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

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $moduleDefinitions = [
            'admin-center' => [
                'name' => 'Admin Center',
                'description' => 'User, Role & Permission management',
                'icon' => '🛠',
                'entry_route' => 'ac.dashboard',
                'sort' => 0,
                'is_active' => true,
            ],
            'project-management' => [
                'name' => 'Project Management',
                'description' => 'Manage projects, milestones, and workflows.',
                'icon' => 'heroicon-o-briefcase',
                'entry_route' => 'pm.dashboard',
                'sort' => 1,
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
        ];

        $allPermissionNames = array_values(array_unique(array_merge($modulePermissions, $globalPermissions)));

        foreach ($allPermissionNames as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

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
            'access project-management',
            'project-management.view',
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

        $menusByModule = [
            'admin-center' => [
                [
                    'label' => 'Dashboard',
                    'route_name' => 'ac.dashboard',
                    'icon' => 'heroicon-o-home',
                    'sort' => 1,
                    'permission_name' => 'admin-center.view',
                    'group' => 'Main',
                    'is_active' => true,
                ],
                [
                    'label' => 'Users',
                    'route_name' => 'ac.users.index',
                    'icon' => 'heroicon-o-users',
                    'sort' => 2,
                    'permission_name' => 'users.view',
                    'group' => 'Main',
                    'is_active' => true,
                ],
                [
                    'label' => 'Roles',
                    'route_name' => 'ac.roles.index',
                    'icon' => 'heroicon-o-shield-check',
                    'sort' => 3,
                    'permission_name' => 'roles.view',
                    'group' => 'Main',
                    'is_active' => true,
                ],
                [
                    'label' => 'Permissions',
                    'route_name' => 'ac.permissions.index',
                    'icon' => 'heroicon-o-key',
                    'sort' => 4,
                    'permission_name' => 'permissions.view',
                    'group' => 'Main',
                    'is_active' => true,
                ],
                [
                    'label' => 'Assign Roles to User',
                    'route_name' => 'ac.assign.user-roles',
                    'icon' => 'heroicon-o-user-plus',
                    'sort' => 1,
                    'permission_name' => 'assignments.manage',
                    'group' => 'Admin',
                    'is_active' => true,
                ],
                [
                    'label' => 'Assign Permissions to Role',
                    'route_name' => 'ac.assign.role-permissions',
                    'icon' => 'heroicon-o-rectangle-group',
                    'sort' => 2,
                    'permission_name' => 'assignments.manage',
                    'group' => 'Admin',
                    'is_active' => true,
                ],
                [
                    'label' => 'Module Access Matrix',
                    'route_name' => 'ac.assign.module-access',
                    'icon' => 'heroicon-o-table-cells',
                    'sort' => 3,
                    'permission_name' => 'assignments.manage',
                    'group' => 'Admin',
                    'is_active' => true,
                ],
            ],
            'project-management' => [
                [
                    'label' => 'Dashboard',
                    'route_name' => 'pm.dashboard',
                    'icon' => 'heroicon-o-home',
                    'sort' => 1,
                    'permission_name' => 'project-management.view',
                    'group' => 'Main',
                    'is_active' => true,
                ],
                [
                    'label' => 'Projects',
                    'route_name' => 'pm.projects.index',
                    'icon' => 'heroicon-o-clipboard-document',
                    'sort' => 2,
                    'permission_name' => 'project-management.view',
                    'group' => 'Main',
                    'is_active' => true,
                ],
                [
                    'label' => 'Create Project',
                    'route_name' => 'pm.projects.create',
                    'icon' => 'heroicon-o-plus',
                    'sort' => 1,
                    'permission_name' => 'project-management.create',
                    'group' => 'Admin',
                    'is_active' => true,
                ],
                [
                    'label' => 'Settings',
                    'route_name' => 'pm.settings',
                    'icon' => 'heroicon-o-cog-6-tooth',
                    'sort' => 2,
                    'permission_name' => 'project-management.edit',
                    'group' => 'Admin',
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
                    ['module_id' => $module->id, 'route_name' => $menu['route_name']],
                    array_merge($menu, ['module_id' => $module->id])
                );
            }
        }

        $schema = [
            'type' => 'wizard',
            'steps' => [
                [
                    'title' => 'Basic Info',
                    'fields' => [
                        [
                            'type' => 'text',
                            'name' => 'project_name',
                            'label' => 'Project Name',
                            'rules' => ['required'],
                        ],
                        [
                            'type' => 'select',
                            'name' => 'project_type',
                            'label' => 'Project Type',
                            'options' => [
                                'internal' => 'Internal',
                                'client' => 'Client',
                            ],
                        ],
                        [
                            'type' => 'text',
                            'name' => 'client_name',
                            'label' => 'Client Name',
                            'visibleWhen' => [
                                'field' => 'project_type',
                                'operator' => 'equals',
                                'value' => 'client',
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Planning',
                    'fields' => [
                        [
                            'type' => 'textarea',
                            'name' => 'description',
                            'label' => 'Description',
                        ],
                        [
                            'type' => 'repeater',
                            'name' => 'milestones',
                            'label' => 'Milestones',
                            'itemSchema' => [
                                [
                                    'type' => 'text',
                                    'name' => 'title',
                                    'label' => 'Milestone Title',
                                ],
                                [
                                    'type' => 'date',
                                    'name' => 'due_date',
                                    'label' => 'Due Date',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        ModuleForm::updateOrCreate(
            ['module_id' => $modules->get('project-management')->id, 'key' => 'project-create'],
            [
                'module_id' => $modules->get('project-management')->id,
                'key' => 'project-create',
                'name' => 'Project Create Wizard',
                'schema_json' => $schema,
                'is_active' => true,
            ]
        );
    }
}
