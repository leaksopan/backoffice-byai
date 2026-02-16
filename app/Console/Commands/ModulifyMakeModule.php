<?php

namespace App\Console\Commands;

use App\Models\Module;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class ModulifyMakeModule extends Command
{
    protected $signature = 'modulify:make
        {name : Human name e.g. "Project Management"}
        {--key= : Optional module key override (kebab-case)}
        {--with-crud : Generate sample CRUD (default ON for this task)}
        {--entity=Item : Entity name for sample CRUD (default "Item")}
        {--sort= : Optional sort_order value; if empty use max(sort_order)+10}
        {--no-seed : Do not run module seeder after generation}
        {--force : If module exists, update DB + overwrite generated files}';

    protected $description = 'Generate a production-grade Modulify module with dashboard, sample CRUD, seeder, and tests.';

    public function handle(): int
    {
        $moduleName = trim((string) $this->argument('name'));
        $moduleClass = Str::studly($moduleName);
        $moduleKey = $this->deriveModuleKey($moduleName, $this->option('key'));
        $force = (bool) $this->option('force');

        if ($moduleClass === '' || $moduleKey === '') {
            $this->error('Module name/key cannot be empty.');

            return self::FAILURE;
        }

        $modulePath = base_path("Modules/{$moduleClass}");
        $moduleExistsOnDisk = File::isDirectory($modulePath);
        $moduleExistsInDatabase = Schema::hasTable('modules')
            && Module::query()->where('key', $moduleKey)->exists();

        if (($moduleExistsOnDisk || $moduleExistsInDatabase) && ! $force) {
            $this->warn("Module [{$moduleClass}] or modules.key [{$moduleKey}] already exists.");
            $this->line('Re-run with --force to overwrite generated files and refresh DB records.');

            return self::FAILURE;
        }

        if (! $moduleExistsOnDisk) {
            $this->info("Creating NWIDART module [{$moduleClass}]...");

            try {
                if ($this->call('module:make', ['name' => [$moduleClass]]) !== self::SUCCESS) {
                    return self::FAILURE;
                }
            } catch (Throwable $exception) {
                if (! File::isDirectory($modulePath)) {
                    $this->error("Failed to create module [{$moduleClass}]: ".$exception->getMessage());

                    return self::FAILURE;
                }

                $this->warn('module:make raised an exception after creating module files. Continuing generation.');
            }
        } else {
            $this->warn('Module folder already exists. Overwriting generated artifacts because --force is set.');
        }

        $this->call('module:enable', ['module' => [$moduleClass]]);

        $entity = Str::studly((string) $this->option('entity') ?: 'Item');
        $entityPluralStudly = Str::pluralStudly($entity);
        $entitySingularLabel = Str::headline($entity);
        $entityPluralLabel = Str::headline($entityPluralStudly);
        $moduleLower = Str::lower($moduleClass);
        $moduleSnakeFromKey = str_replace('-', '_', $moduleKey);
        $itemsTable = "{$moduleSnakeFromKey}_".Str::snake($entityPluralStudly);
        $moduleModelClass = "{$moduleClass}{$entity}";
        $sortOrder = $this->resolveSortOrder($this->option('sort'));

        $this->generateModuleFiles(
            modulePath: $modulePath,
            moduleClass: $moduleClass,
            moduleKey: $moduleKey,
            moduleName: $moduleName,
            moduleLower: $moduleLower,
            entity: $entity,
            entitySingularLabel: $entitySingularLabel,
            entityPluralLabel: $entityPluralLabel,
            moduleModelClass: $moduleModelClass,
            itemsTable: $itemsTable,
            sortOrder: $sortOrder,
        );

        $this->ensurePhpUnitDiscoversModules();

        $moduleMigrationPath = "Modules/{$moduleClass}/database/migrations";
        $this->info("Running migrations from [{$moduleMigrationPath}]...");

        if ($this->call('migrate', ['--path' => $moduleMigrationPath]) !== self::SUCCESS) {
            $this->error('Migration failed.');

            return self::FAILURE;
        }

        if (! $this->option('no-seed')) {
            $seederClass = "Modules\\{$moduleClass}\\Database\\Seeders\\{$moduleClass}Seeder";
            $seederPath = "{$modulePath}/database/seeders/{$moduleClass}Seeder.php";

            if (! class_exists($seederClass) && File::exists($seederPath)) {
                require_once $seederPath;
            }

            $this->info("Seeding [{$seederClass}]...");

            if ($this->call('db:seed', ['--class' => $seederClass]) !== self::SUCCESS) {
                $this->error('Seeder execution failed.');

                return self::FAILURE;
            }
        }

        $this->newLine();
        $this->info("Module [{$moduleClass}] generated successfully.");
        $this->line("Key: {$moduleKey}");
        $this->line("Entry route: {$moduleKey}.dashboard");
        $this->line("URL: /m/{$moduleKey}/dashboard");

        return self::SUCCESS;
    }

    private function deriveModuleKey(string $name, mixed $override): string
    {
        $key = is_string($override) && trim($override) !== ''
            ? trim($override)
            : $name;

        return Str::slug($key);
    }

    private function resolveSortOrder(mixed $option): int
    {
        if ($option !== null && $option !== '') {
            return max((int) $option, 0);
        }

        if (! Schema::hasTable('modules')) {
            return 10;
        }

        $maxSort = (int) Module::query()->max('sort_order');

        return $maxSort + 10;
    }

    private function generateModuleFiles(
        string $modulePath,
        string $moduleClass,
        string $moduleKey,
        string $moduleName,
        string $moduleLower,
        string $entity,
        string $entitySingularLabel,
        string $entityPluralLabel,
        string $moduleModelClass,
        string $itemsTable,
        int $sortOrder
    ): void {
        $this->syncRouteServiceProvider($modulePath, $moduleClass);

        $this->putFile("{$modulePath}/Routes/web.php", $this->routeWebStub($moduleClass, $moduleKey));
        $this->putFile("{$modulePath}/Routes/api.php", $this->routeApiStub());
        $this->putFile("{$modulePath}/app/Http/Controllers/DashboardController.php", $this->dashboardControllerStub($moduleClass, $moduleLower));
        $this->putFile("{$modulePath}/app/Http/Controllers/ItemController.php", $this->itemControllerStub($moduleClass, $moduleKey, $entitySingularLabel, $moduleModelClass));
        $this->putFile("{$modulePath}/app/Models/{$moduleModelClass}.php", $this->itemModelStub($moduleClass, $moduleModelClass, $itemsTable));

        $this->cleanupLegacyMigrations($modulePath, $itemsTable);
        $this->putFile("{$modulePath}/database/migrations/2026_01_01_000000_create_{$itemsTable}_table.php", $this->itemMigrationStub($itemsTable));

        $this->putFile("{$modulePath}/resources/views/dashboard.blade.php", $this->dashboardViewStub($moduleName, $moduleKey, $entityPluralLabel));
        $this->putFile("{$modulePath}/resources/views/items/index.blade.php", $this->itemIndexViewStub($moduleKey, $entitySingularLabel, $entityPluralLabel));
        $this->putFile("{$modulePath}/resources/views/items/create.blade.php", $this->itemCreateViewStub($moduleKey, $entitySingularLabel));
        $this->putFile("{$modulePath}/resources/views/items/edit.blade.php", $this->itemEditViewStub($moduleKey, $entitySingularLabel));
        $this->putFile("{$modulePath}/database/seeders/{$moduleClass}Seeder.php", $this->moduleSeederStub($moduleClass, $moduleKey, $moduleName, $sortOrder));
        $this->putFile("{$modulePath}/tests/Feature/AccessTest.php", $this->accessTestStub($moduleClass, $moduleKey));
        $this->putFile("{$modulePath}/tests/Feature/CrudSmokeTest.php", $this->crudSmokeTestStub($moduleClass, $moduleKey, $moduleModelClass, $itemsTable, $entitySingularLabel));
    }

    private function syncRouteServiceProvider(string $modulePath, string $moduleClass): void
    {
        $providerPath = "{$modulePath}/app/Providers/RouteServiceProvider.php";

        if (! File::exists($providerPath)) {
            return;
        }

        $provider = File::get($providerPath);
        $provider = str_replace('/routes/web.php', '/Routes/web.php', $provider);
        $provider = str_replace('/routes/api.php', '/Routes/api.php', $provider);

        File::put($providerPath, $provider);
        File::ensureDirectoryExists("{$modulePath}/Routes");
    }

    private function cleanupLegacyMigrations(string $modulePath, string $itemsTable): void
    {
        $paths = [
            ...glob("{$modulePath}/database/migrations/*_create_{$itemsTable}_table.php") ?: [],
            ...glob("{$modulePath}/Database/Migrations/*_create_{$itemsTable}_table.php") ?: [],
        ];

        foreach ($paths as $path) {
            File::delete($path);
        }
    }

    private function ensurePhpUnitDiscoversModules(): void
    {
        $path = base_path('phpunit.xml');

        if (! File::exists($path)) {
            return;
        }

        $xml = File::get($path);

        if (str_contains($xml, '<directory suffix="Test.php">Modules</directory>')) {
            return;
        }

        $updated = preg_replace(
            '/(<testsuite name="Feature">\s*<directory>tests\/Feature<\/directory>)/',
            '$1'.PHP_EOL.'            <directory suffix="Test.php">Modules</directory>',
            $xml,
            1
        );

        if (is_string($updated) && $updated !== $xml) {
            File::put($path, $updated);
        }
    }

    private function putFile(string $path, string $content): void
    {
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);
    }

    private function routeWebStub(string $moduleClass, string $moduleKey): string
    {
        return <<<PHP
<?php

use Illuminate\\Support\\Facades\\Route;
use Modules\\{$moduleClass}\\Http\\Controllers\\DashboardController;
use Modules\\{$moduleClass}\\Http\\Controllers\\ItemController;

Route::prefix('m/{$moduleKey}')
    ->middleware(['web', 'auth', 'ensure.module.access'])
    ->name('{$moduleKey}.')
    ->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard')
            ->middleware('can:{$moduleKey}.view')
            ->defaults('moduleKey', '{$moduleKey}');

        Route::get('/items', [ItemController::class, 'index'])
            ->name('items.index')
            ->middleware('can:{$moduleKey}.view')
            ->defaults('moduleKey', '{$moduleKey}');

        Route::get('/items/create', [ItemController::class, 'create'])
            ->name('items.create')
            ->middleware('can:{$moduleKey}.create')
            ->defaults('moduleKey', '{$moduleKey}');

        Route::post('/items', [ItemController::class, 'store'])
            ->name('items.store')
            ->middleware('can:{$moduleKey}.create')
            ->defaults('moduleKey', '{$moduleKey}');

        Route::get('/items/{id}/edit', [ItemController::class, 'edit'])
            ->name('items.edit')
            ->middleware('can:{$moduleKey}.edit')
            ->defaults('moduleKey', '{$moduleKey}');

        Route::put('/items/{id}', [ItemController::class, 'update'])
            ->name('items.update')
            ->middleware('can:{$moduleKey}.edit')
            ->defaults('moduleKey', '{$moduleKey}');

        Route::delete('/items/{id}', [ItemController::class, 'destroy'])
            ->name('items.destroy')
            ->middleware('can:{$moduleKey}.delete')
            ->defaults('moduleKey', '{$moduleKey}');
    });
PHP;
    }

    private function routeApiStub(): string
    {
        return <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('v1')->group(function (): void {
    // Module API routes.
});
PHP;
    }

    private function dashboardControllerStub(string $moduleClass, string $moduleLower): string
    {
        return <<<PHP
<?php

namespace Modules\\{$moduleClass}\\Http\\Controllers;

use Illuminate\\Contracts\\View\\View;

class DashboardController
{
    public function index(): View
    {
        return view('{$moduleLower}::dashboard');
    }
}
PHP;
    }

    private function itemControllerStub(string $moduleClass, string $moduleKey, string $entitySingularLabel, string $moduleModelClass): string
    {
        $moduleClassLower = Str::lower($moduleClass);

        return <<<PHP
<?php

namespace Modules\\{$moduleClass}\\Http\\Controllers;

use Illuminate\\Contracts\\View\\View;
use Illuminate\\Http\\RedirectResponse;
use Illuminate\\Http\\Request;
use Modules\\{$moduleClass}\\Models\\{$moduleModelClass};

class ItemController
{
    public function index(): View
    {
        \$items = {$moduleModelClass}::query()->latest('updated_at')->get();

        return view('{$moduleClassLower}::items.index', [
            'items' => \$items,
        ]);
    }

    public function create(): View
    {
        return view('{$moduleClassLower}::items.create');
    }

    public function store(Request \$request): RedirectResponse
    {
        \$validated = \$request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:100'],
        ]);

        {$moduleModelClass}::query()->create(\$validated);

        return redirect()
            ->route('{$moduleKey}.items.index')
            ->with('status', '{$entitySingularLabel} created successfully.');
    }

    public function edit(int \$id): View
    {
        \$item = {$moduleModelClass}::query()->findOrFail(\$id);

        return view('{$moduleClassLower}::items.edit', [
            'item' => \$item,
        ]);
    }

    public function update(Request \$request, int \$id): RedirectResponse
    {
        \$item = {$moduleModelClass}::query()->findOrFail(\$id);

        \$validated = \$request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:100'],
        ]);

        \$item->update(\$validated);

        return redirect()
            ->route('{$moduleKey}.items.index')
            ->with('status', '{$entitySingularLabel} updated successfully.');
    }

    public function destroy(int \$id): RedirectResponse
    {
        \$item = {$moduleModelClass}::query()->findOrFail(\$id);
        \$item->delete();

        return redirect()
            ->route('{$moduleKey}.items.index')
            ->with('status', '{$entitySingularLabel} deleted successfully.');
    }
}
PHP;
    }

    private function itemModelStub(string $moduleClass, string $moduleModelClass, string $itemsTable): string
    {
        return <<<PHP
<?php

namespace Modules\\{$moduleClass}\\Models;

use Illuminate\\Database\\Eloquent\\Model;

class {$moduleModelClass} extends Model
{
    protected \$table = '{$itemsTable}';

    protected \$fillable = [
        'name',
        'status',
    ];
}
PHP;
    }

    private function itemMigrationStub(string $itemsTable): string
    {
        return <<<PHP
<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('{$itemsTable}')) {
            return;
        }

        Schema::create('{$itemsTable}', function (Blueprint \$table): void {
            \$table->id();
            \$table->string('name');
            \$table->string('status')->default('active');
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$itemsTable}');
    }
};
PHP;
    }

    private function dashboardViewStub(string $moduleName, string $moduleKey, string $entityPluralLabel): string
    {
        return <<<BLADE
@extends('layouts.module')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-50">{$moduleName} Dashboard</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Module generated by <code>modulify:make</code>.</p>
        </div>

        <div class="rounded-2xl border border-slate-200/80 glass-panel p-6 dark:border-slate-700/80">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-50">Quick Access</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Manage {$entityPluralLabel} in this module.</p>
                </div>
                <a class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-sky-600 dark:hover:bg-sky-500" href="{{ route('{$moduleKey}.items.index') }}">
                    Open {$entityPluralLabel}
                </a>
            </div>
        </div>
    </div>
@endsection
BLADE;
    }

    private function itemIndexViewStub(string $moduleKey, string $entitySingularLabel, string $entityPluralLabel): string
    {
        return <<<BLADE
@extends('layouts.module')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-50">{$entityPluralLabel}</h1>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Sample CRUD listing generated by <code>modulify:make</code>.</p>
            </div>
            <a class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-sky-600 dark:hover:bg-sky-500" href="{{ route('{$moduleKey}.items.create') }}">
                Create {$entitySingularLabel}
            </a>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 glass-panel dark:border-slate-700/80">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50/80 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:bg-slate-900/70 dark:text-slate-300">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Updated</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse (\$items as \$item)
                        <tr>
                            <td class="px-4 py-3 text-slate-800 dark:text-slate-100">{{ \$item->name }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ \$item->status }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ \$item->updated_at?->diffForHumans() }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <a class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800" href="{{ route('{$moduleKey}.items.edit', \$item->id) }}">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('{$moduleKey}.items.destroy', \$item->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-lg border border-rose-300 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-50 dark:border-rose-700 dark:text-rose-300 dark:hover:bg-rose-900/30" type="submit">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">No {$entityPluralLabel} found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
BLADE;
    }

    private function itemCreateViewStub(string $moduleKey, string $entitySingularLabel): string
    {
        return <<<BLADE
@extends('layouts.module')

@section('content')
    <div class="max-w-2xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-50">Create {$entitySingularLabel}</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Add a new {$entitySingularLabel} record.</p>
        </div>

        <form class="space-y-6 rounded-2xl border border-slate-200/80 glass-panel p-6 dark:border-slate-700/80" method="POST" action="{{ route('{$moduleKey}.items.store') }}">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Name</label>
                <input class="mt-2 w-full rounded-lg border-slate-300 bg-white/70 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-900/70" name="name" type="text" value="{{ old('name') }}" required>
                @error('name') <p class="mt-1 text-xs text-rose-600">{{ \$message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Status</label>
                <input class="mt-2 w-full rounded-lg border-slate-300 bg-white/70 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-900/70" name="status" type="text" value="{{ old('status', 'active') }}" required>
                @error('status') <p class="mt-1 text-xs text-rose-600">{{ \$message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3">
                <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-sky-600 dark:hover:bg-sky-500" type="submit">
                    Save
                </button>
                <a class="text-sm text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100" href="{{ route('{$moduleKey}.items.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
BLADE;
    }

    private function itemEditViewStub(string $moduleKey, string $entitySingularLabel): string
    {
        return <<<BLADE
@extends('layouts.module')

@section('content')
    <div class="max-w-2xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-50">Edit {$entitySingularLabel}</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Update {$entitySingularLabel} details.</p>
        </div>

        <form class="space-y-6 rounded-2xl border border-slate-200/80 glass-panel p-6 dark:border-slate-700/80" method="POST" action="{{ route('{$moduleKey}.items.update', \$item->id) }}">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Name</label>
                <input class="mt-2 w-full rounded-lg border-slate-300 bg-white/70 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-900/70" name="name" type="text" value="{{ old('name', \$item->name) }}" required>
                @error('name') <p class="mt-1 text-xs text-rose-600">{{ \$message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Status</label>
                <input class="mt-2 w-full rounded-lg border-slate-300 bg-white/70 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-900/70" name="status" type="text" value="{{ old('status', \$item->status) }}" required>
                @error('status') <p class="mt-1 text-xs text-rose-600">{{ \$message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3">
                <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-sky-600 dark:hover:bg-sky-500" type="submit">
                    Update
                </button>
                <a class="text-sm text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100" href="{{ route('{$moduleKey}.items.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
BLADE;
    }

    private function moduleSeederStub(string $moduleClass, string $moduleKey, string $moduleName, int $sortOrder): string
    {
        return <<<PHP
<?php

namespace Modules\\{$moduleClass}\\Database\\Seeders;

use App\\Models\\Module;
use App\\Models\\ModuleMenu;
use Illuminate\\Database\\Seeder;
use Spatie\\Permission\\Models\\Permission;
use Spatie\\Permission\\Models\\Role;
use Spatie\\Permission\\PermissionRegistrar;

class {$moduleClass}Seeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Module::query()->updateOrCreate(
            ['key' => '{$moduleKey}'],
            [
                'name' => '{$moduleName}',
                'description' => 'Module generated by modulify:make',
                'icon' => '🧩',
                'entry_route' => '{$moduleKey}.dashboard',
                'sort_order' => {$sortOrder},
                'is_active' => true,
            ]
        );

        \$permissions = [
            'access {$moduleKey}',
            '{$moduleKey}.view',
            '{$moduleKey}.create',
            '{$moduleKey}.edit',
            '{$moduleKey}.delete',
        ];

        foreach (\$permissions as \$permissionName) {
            Permission::firstOrCreate([
                'name' => \$permissionName,
                'guard_name' => 'web',
            ]);
        }

        \$menus = [
            [
                'label' => 'Dashboard',
                'route_name' => '{$moduleKey}.dashboard',
                'icon' => 'heroicon-o-home',
                'permission_name' => '{$moduleKey}.view',
                'sort_order' => 10,
            ],
            [
                'label' => 'Items',
                'route_name' => '{$moduleKey}.items.index',
                'icon' => 'heroicon-o-clipboard-document',
                'permission_name' => '{$moduleKey}.view',
                'sort_order' => 20,
            ],
            [
                'label' => 'Create Item',
                'route_name' => '{$moduleKey}.items.create',
                'icon' => 'heroicon-o-plus',
                'permission_name' => '{$moduleKey}.create',
                'sort_order' => 30,
            ],
        ];

        foreach (\$menus as \$menu) {
            ModuleMenu::query()->updateOrCreate(
                ['module_key' => '{$moduleKey}', 'label' => \$menu['label']],
                [
                    'module_key' => '{$moduleKey}',
                    'section' => 'MAIN',
                    'label' => \$menu['label'],
                    'route_name' => \$menu['route_name'],
                    'url' => null,
                    'icon' => \$menu['icon'],
                    'permission_name' => \$menu['permission_name'],
                    'sort_order' => \$menu['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        foreach (['super-admin', 'admin'] as \$roleName) {
            \$role = Role::query()
                ->where('name', \$roleName)
                ->where('guard_name', 'web')
                ->first();

            if (\$role) {
                \$role->givePermissionTo(\$permissions);
            }
        }
    }
}
PHP;
    }

    private function accessTestStub(string $moduleClass, string $moduleKey): string
    {
        return <<<PHP
<?php

namespace Modules\\{$moduleClass}\\Tests\\Feature;

use App\\Models\\User;
use Database\\Seeders\\DatabaseSeeder;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Tests\\TestCase;

class AccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! class_exists(\\Modules\\{$moduleClass}\\Database\\Seeders\\{$moduleClass}Seeder::class)) {
            require_once base_path('Modules/{$moduleClass}/database/seeders/{$moduleClass}Seeder.php');
        }

        \$this->seed(DatabaseSeeder::class);
        \$this->seed(\\Modules\\{$moduleClass}\\Database\\Seeders\\{$moduleClass}Seeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        \$this->get('/m/{$moduleKey}/dashboard')
            ->assertRedirect('/login');
    }

    public function test_user_without_access_permission_gets_forbidden(): void
    {
        \$user = User::factory()->create();

        \$this->actingAs(\$user)
            ->get('/m/{$moduleKey}/dashboard')
            ->assertForbidden();
    }

    public function test_user_with_access_and_view_permissions_can_open_dashboard(): void
    {
        \$user = User::factory()->create();
        \$user->givePermissionTo('access {$moduleKey}', '{$moduleKey}.view');

        \$this->actingAs(\$user)
            ->get('/m/{$moduleKey}/dashboard')
            ->assertOk();
    }
}
PHP;
    }

    private function crudSmokeTestStub(string $moduleClass, string $moduleKey, string $moduleModelClass, string $itemsTable, string $entitySingularLabel): string
    {
        return <<<PHP
<?php

namespace Modules\\{$moduleClass}\\Tests\\Feature;

use App\\Models\\User;
use Database\\Seeders\\DatabaseSeeder;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Modules\\{$moduleClass}\\Models\\{$moduleModelClass};
use Tests\\TestCase;

class CrudSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! class_exists(\\Modules\\{$moduleClass}\\Database\\Seeders\\{$moduleClass}Seeder::class)) {
            require_once base_path('Modules/{$moduleClass}/database/seeders/{$moduleClass}Seeder.php');
        }

        \$this->seed(DatabaseSeeder::class);
        \$this->seed(\\Modules\\{$moduleClass}\\Database\\Seeders\\{$moduleClass}Seeder::class);
    }

    public function test_user_with_access_view_and_create_can_store_item(): void
    {
        \$user = User::factory()->create();
        \$user->givePermissionTo('access {$moduleKey}', '{$moduleKey}.view', '{$moduleKey}.create');

        \$response = \$this->actingAs(\$user)->post('/m/{$moduleKey}/items', [
            'name' => 'Generated {$entitySingularLabel}',
            'status' => 'active',
        ]);

        \$response->assertRedirect('/m/{$moduleKey}/items');
        \$this->assertDatabaseHas('{$itemsTable}', ['name' => 'Generated {$entitySingularLabel}']);
    }

    public function test_user_with_view_permission_can_see_index_and_item_rows(): void
    {
        \$user = User::factory()->create();
        \$user->givePermissionTo('access {$moduleKey}', '{$moduleKey}.view');

        {$moduleModelClass}::query()->create([
            'name' => 'Visible {$entitySingularLabel}',
            'status' => 'active',
        ]);

        \$this->actingAs(\$user)
            ->get('/m/{$moduleKey}/items')
            ->assertOk()
            ->assertSee('Visible {$entitySingularLabel}');
    }
}
PHP;
    }
}
