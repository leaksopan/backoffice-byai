<?php

namespace App\Providers;

use App\Models\Module;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $helpersPath = app_path('Support/helpers.php');

        if (file_exists($helpersPath)) {
            require_once $helpersPath;
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.module', function ($view): void {
            $module = request()->attributes->get('activeModule');
            $moduleKey = request()->route('moduleKey');

            if (! $moduleKey && $module instanceof Module) {
                $moduleKey = $module->key;
            }

            $menuGroups = collect();
            $showAdminGroup = false;

            if (! $module && $moduleKey) {
                $module = Module::query()->where('key', $moduleKey)->first();
            }

            if ($module) {
                $menus = $module->menus()
                    ->where('is_active', true)
                    ->orderBy('section')
                    ->orderBy('sort_order')
                    ->get()
                    ->filter(function ($menu) {
                        if (! Route::has($menu->route_name)) {
                            return false;
                        }

                        if (! $menu->permission_name) {
                            return true;
                        }

                        return Auth::check() && Gate::allows($menu->permission_name);
                    });

                $menuGroups = $menus->groupBy(function ($menu) {
                    return $menu->section ?: 'MAIN';
                });

                $showAdminGroup = Auth::check() && (
                    Gate::allows($module->key.'.create')
                    || Gate::allows($module->key.'.edit')
                    || Gate::allows($module->key.'.delete')
                );
            }

            $view->with([
                'activeModule' => $module,
                'menuGroups' => $menuGroups,
                'showAdminGroup' => $showAdminGroup,
            ]);
        });
    }
}
