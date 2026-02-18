<?php

namespace Modules\MasterDataManagement\Http\Controllers;

use App\Http\Controllers\Controller;

abstract class MdmBaseController extends Controller
{
    protected string $moduleKey = 'master-data-management';
}
