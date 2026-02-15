<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('module_menus')) {
            return;
        }

        if (Schema::hasTable('module_menus_legacy')) {
            Schema::drop('module_menus_legacy');
        }

        $legacyMenus = DB::table('module_menus')->get();
        $moduleKeysById = DB::table('modules')->pluck('key', 'id');

        Schema::rename('module_menus', 'module_menus_legacy');

        Schema::create('module_menus', function (Blueprint $table): void {
            $table->id();
            $table->string('module_key');
            $table->string('section')->default('MAIN');
            $table->string('label');
            $table->string('route_name')->nullable();
            $table->string('url')->nullable();
            $table->string('icon')->nullable();
            $table->string('permission_name')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['module_key', 'section', 'sort_order']);
        });

        foreach ($legacyMenus as $menu) {
            $moduleKey = $moduleKeysById->get($menu->module_id) ?? ($menu->module_key ?? null);

            if (! $moduleKey) {
                continue;
            }

            DB::table('module_menus')->insert([
                'module_key' => $moduleKey,
                'section' => strtoupper((string) ($menu->group ?? $menu->section ?? 'MAIN')),
                'label' => $menu->label,
                'route_name' => $menu->route_name ?? null,
                'url' => $menu->url ?? null,
                'icon' => $menu->icon ?? null,
                'permission_name' => $menu->permission_name ?? null,
                'sort_order' => (int) ($menu->sort ?? $menu->sort_order ?? 0),
                'is_active' => (bool) ($menu->is_active ?? true),
                'created_at' => $menu->created_at ?? now(),
                'updated_at' => $menu->updated_at ?? now(),
            ]);
        }

        Schema::drop('module_menus_legacy');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('module_menus')) {
            return;
        }

        if (Schema::hasTable('module_menus_v2')) {
            Schema::drop('module_menus_v2');
        }

        $menus = DB::table('module_menus')->get();
        $moduleIdsByKey = DB::table('modules')->pluck('id', 'key');

        Schema::rename('module_menus', 'module_menus_v2');

        Schema::create('module_menus', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->string('label');
            $table->string('route_name');
            $table->string('icon')->nullable();
            $table->integer('sort')->default(0);
            $table->string('permission_name')->nullable();
            $table->string('group')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        foreach ($menus as $menu) {
            $moduleId = $moduleIdsByKey->get($menu->module_key);

            if (! $moduleId) {
                continue;
            }

            DB::table('module_menus')->insert([
                'module_id' => $moduleId,
                'label' => $menu->label,
                'route_name' => $menu->route_name ?? '#',
                'icon' => $menu->icon ?? null,
                'sort' => (int) ($menu->sort_order ?? 0),
                'permission_name' => $menu->permission_name ?? null,
                'group' => ucfirst(strtolower((string) ($menu->section ?? 'MAIN'))),
                'is_active' => (bool) ($menu->is_active ?? true),
                'created_at' => $menu->created_at ?? now(),
                'updated_at' => $menu->updated_at ?? now(),
            ]);
        }

        Schema::drop('module_menus_v2');
    }
};
