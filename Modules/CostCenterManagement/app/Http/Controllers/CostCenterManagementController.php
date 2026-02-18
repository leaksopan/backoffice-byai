<?php

namespace Modules\CostCenterManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CostCenterManagementController extends Controller
{
    public function index()
    {
        return view('costcentermanagement::index');
    }
}
