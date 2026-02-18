<?php

namespace Modules\StrategicManagement\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\StrategicManagement\Models\SmGoal;
use Modules\StrategicManagement\Models\SmKpi;

class SmKpisController
{
    public function index(): View
    {
        $kpis = SmKpi::with(['goal.vision', 'actuals'])
            ->orderBy('year', 'desc')
            ->orderBy('code')
            ->get();

        return view('strategicmanagement::kpis.index', compact('kpis'));
    }

    public function create(): View
    {
        $goals = SmGoal::with('vision')
            ->orderBy('code')
            ->get();

        return view('strategicmanagement::kpis.create', compact('goals'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'goal_id'        => 'required|exists:sm_goals,id',
            'code'           => 'required|string|max:50',
            'name'           => 'required|string|max:255',
            'unit'           => 'required|string|max:50',
            'target_value'   => 'required|numeric|min:0',
            'baseline_value' => 'nullable|numeric|min:0',
            'formula'        => 'nullable|string|max:255',
            'year'           => 'required|digits:4',
        ]);

        SmKpi::create($validated);

        return redirect()->route('sm.kpis.index')
            ->with('success', 'KPI berhasil ditambahkan.');
    }
}
