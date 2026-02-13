<?php

namespace Modules\ProjectManagement\Http\Controllers;

use App\Models\ModuleForm;
use Illuminate\Contracts\View\View;

class PmProjectsController
{
    public function index(): View
    {
        return view('projectmanagement::projects.index');
    }

    public function create(): View
    {
        $moduleForm = ModuleForm::query()
            ->whereHas('module', function ($query) {
                $query->where('key', 'project-management');
            })
            ->where('key', 'project-create')
            ->where('is_active', true)
            ->first();

        return view('projectmanagement::projects.create', [
            'moduleForm' => $moduleForm,
        ]);
    }
}
