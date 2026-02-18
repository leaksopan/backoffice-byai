<?php

namespace Modules\StrategicManagement\Http\Controllers;

use Illuminate\Contracts\View\View;
use Modules\StrategicManagement\Models\SmEvaluation;
use Modules\StrategicManagement\Models\SmKpi;
use Modules\StrategicManagement\Models\SmRoadmap;
use Modules\StrategicManagement\Models\SmVision;

class SmDashboardController
{
    public function index(): View
    {
        $activeVisions = SmVision::where('is_active', true)->count();
        $totalKpis     = SmKpi::count();
        $avgScore      = SmKpi::join('sm_kpi_actuals', 'sm_kpis.id', '=', 'sm_kpi_actuals.kpi_id')
            ->selectRaw('CASE WHEN SUM(sm_kpis.target_value) > 0 THEN ROUND(SUM(sm_kpi_actuals.actual_value) / SUM(sm_kpis.target_value) * 100, 1) ELSE 0 END as score')
            ->value('score') ?? 0;
        $roadmapItems  = SmRoadmap::count();

        return view('strategicmanagement::dashboard', compact(
            'activeVisions',
            'totalKpis',
            'avgScore',
            'roadmapItems',
        ));
    }
}
