<?php

use App\Models\AppSetting;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return AppSetting::getValue($key, $default);
    }
}

if (! function_exists('nav_is_active')) {
    function nav_is_active(string|array|null $patternsOrRouteNames): bool
    {
        if (! $patternsOrRouteNames) {
            return false;
        }

        $patterns = is_array($patternsOrRouteNames)
            ? $patternsOrRouteNames
            : [$patternsOrRouteNames];

        foreach ($patterns as $pattern) {
            if (! is_string($pattern)) {
                continue;
            }

            $candidate = trim($pattern);

            if ($candidate === '') {
                continue;
            }

            if (str_starts_with($candidate, 'http://') || str_starts_with($candidate, 'https://')) {
                $candidate = (string) parse_url($candidate, PHP_URL_PATH);
            }

            $candidate = ltrim($candidate, '/');

            if ($candidate === '') {
                continue;
            }

            if (request()->routeIs($candidate) || request()->is($candidate)) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('nav_active_class')) {
    function nav_active_class(
        string|array|null $patternsOrRouteNames,
        string $active = 'glass-soft ring-1 ring-sky-400/50 text-sky-700 dark:text-sky-200',
        string $inactive = 'text-slate-700 dark:text-slate-200 hover:glass-soft'
    ): string {
        return nav_is_active($patternsOrRouteNames) ? $active : $inactive;
    }
}
