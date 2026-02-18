<?php

namespace Modules\StrategicManagement\Http\Controllers;

use Illuminate\Contracts\View\View;
use Modules\StrategicManagement\Models\SmEvaluation;

class SmEvaluationsController
{
    public function index(): View
    {
        $evaluations = SmEvaluation::with(['vision', 'evaluator'])
            ->orderByDesc('year')
            ->get();

        return view('strategicmanagement::evaluations.index', compact('evaluations'));
    }
}
