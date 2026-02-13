<?php

namespace Modules\ProjectManagement\Http\Controllers;

use Illuminate\Contracts\View\View;

class PmSettingsController
{
    public function index(): View
    {
        return view('projectmanagement::settings');
    }
}
