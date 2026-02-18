<?php

namespace Modules\StrategicManagement\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\StrategicManagement\Models\SmGoal;
use Modules\StrategicManagement\Models\SmVision;

class SmVisionsController
{
    public function index(): View
    {
        $visions = SmVision::with('goals')
            ->orderByDesc('is_active')
            ->orderByDesc('period_start')
            ->get();

        return view('strategicmanagement::visions.index', compact('visions'));
    }

    public function create(): View
    {
        return view('strategicmanagement::visions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'vision_text'  => 'required|string',
            'mission_text' => 'required|string',
            'period_start' => 'required|digits:4',
            'period_end'   => 'required|digits:4|gte:period_start',
            'is_active'    => 'boolean',
            'goals'        => 'nullable|array',
            'goals.*.code' => 'required_with:goals|string|max:50',
            'goals.*.name' => 'required_with:goals|string|max:255',
        ]);

        $vision = SmVision::create([
            'title'        => $validated['title'],
            'vision_text'  => $validated['vision_text'],
            'mission_text' => $validated['mission_text'],
            'period_start' => $validated['period_start'],
            'period_end'   => $validated['period_end'],
            'is_active'    => $validated['is_active'] ?? true,
        ]);

        if (! empty($validated['goals'])) {
            foreach ($validated['goals'] as $i => $goalData) {
                $vision->goals()->create(array_merge($goalData, ['sort' => $i]));
            }
        }

        return redirect()->route('sm.visions.index')
            ->with('success', 'Visi & Misi berhasil disimpan.');
    }

    public function edit(int $id): View
    {
        $vision = SmVision::with('goals')->findOrFail($id);

        return view('strategicmanagement::visions.edit', compact('vision'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $vision = SmVision::findOrFail($id);

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'vision_text'  => 'required|string',
            'mission_text' => 'required|string',
            'period_start' => 'required|digits:4',
            'period_end'   => 'required|digits:4|gte:period_start',
            'is_active'    => 'boolean',
        ]);

        $vision->update($validated);

        return redirect()->route('sm.visions.index')
            ->with('success', 'Visi & Misi berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $vision = SmVision::findOrFail($id);
        $vision->delete();

        return redirect()->route('sm.visions.index')
            ->with('success', 'Visi & Misi berhasil dihapus.');
    }
}
