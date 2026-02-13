<?php

namespace Modules\ProjectManagement\Http\Controllers;

use Illuminate\Contracts\View\View;

class PmDashboardController
{
    public function index(): View
    {
        return view('projectmanagement::dashboard');
    }
}
