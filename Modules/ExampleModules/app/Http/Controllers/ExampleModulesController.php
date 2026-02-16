<?php

namespace Modules\ExampleModules\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class ExampleModulesController extends Controller
{
    public function dashboard(): View
    {
        return view('examplemodules::dashboard');
    }

    public function files(): View
    {
        return view('examplemodules::files');
    }

    public function sidebar(): View
    {
        return view('examplemodules::sidebar');
    }
}
