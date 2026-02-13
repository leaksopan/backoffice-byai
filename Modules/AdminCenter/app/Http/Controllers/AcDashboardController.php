<?php

namespace Modules\AdminCenter\Http\Controllers;

use Illuminate\Contracts\View\View;

class AcDashboardController
{
    public function index(): View
    {
        return view('admincenter::dashboard');
    }
}
