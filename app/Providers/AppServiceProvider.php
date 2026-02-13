<?php

namespace App\Providers;

use App\Models\Module;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.module', function ($view): void {
            $moduleKey = request()->route('moduleKey');
            $module = null;
            $menuGroups = collect();
            $showAdminGroup = false;

            if ($moduleKey) {
                $module = Module::query()->where('key', $moduleKey)->first();

                if ($module) {
                    $menus = $module->menus()
                        ->where('is_active', true)
                        ->orderBy('sort')
                        ->get()
                        ->filter(function ($menu) {
                            if (! $menu->permission_name) {
                                return true;
                            }

                            return auth()->check() && auth()->user()->can($menu->permission_name);
                        });

                    $menuGroups = $menus->groupBy(function ($menu) {
                        return $menu->group ?: 'Main';
                    });

                    $showAdminGroup = auth()->check() && (
                        auth()->user()->can($moduleKey.'.create')
                        || auth()->user()->can($moduleKey.'.edit')
                        || auth()->user()->can($moduleKey.'.delete')
                    );
                }
            }

            $view->with([
                'activeModule' => $module,
                'menuGroups' => $menuGroups,
                'showAdminGroup' => $showAdminGroup,
            ]);
        });
    }
}
