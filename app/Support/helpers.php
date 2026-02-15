<?php

use App\Models\AppSetting;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return AppSetting::getValue($key, $default);
    }
}
