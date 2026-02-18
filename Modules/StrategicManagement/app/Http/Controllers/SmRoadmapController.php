<?php

namespace Modules\StrategicManagement\Http\Controllers;

use Illuminate\Contracts\View\View;
use Modules\StrategicManagement\Models\SmRoadmap;

class SmRoadmapController
{
    public function index(): View
    {
        $roadmaps = SmRoadmap::with('goal.vision')
            ->orderBy('year')
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 END")
            ->get()
            ->groupBy('year');

        return view('strategicmanagement::roadmap.index', compact('roadmaps'));
    }
}
