<?php

namespace Modules\StrategicManagement\Http\Controllers;

use Illuminate\Contracts\View\View;

class SmSettingsController
{
    public function index(): View
    {
        return view('strategicmanagement::settings');
    }
}
