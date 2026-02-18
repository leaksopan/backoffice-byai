<?php

namespace Modules\CostCenterManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\CostCenterManagement\Services\AuditTrailService;
use Modules\CostCenterManagement\Models\AuditLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AuditTrailController extends Controller
{
    protected AuditTrailService $auditService;
    
    public function __construct(AuditTrailService $auditService)
    {
        $this->auditService = $auditService;
    }
    
    public function index(Request $request)
    {
        $validated = $request->validate([
            'model_type' => 'nullable|string',
            'model_id' => 'nullable|integer',
            'event' => 'nullable|string',
            'user_id' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'per_page' => 'nullable|integer|min:10|max:100',
        ]);
        
        $logs = $this->auditService->getAuditTrail(
            $validated['model_type'] ?? null,
            $validated['model_id'] ?? null,
            $validated['event'] ?? null,
            $validated['user_id'] ?? null,
            isset($validated['start_date']) ? Carbon::parse($validated['start_date']) : null,
            isset($validated['end_date']) ? Carbon::parse($validated['end_date']) : null,
            $validated['per_page'] ?? 50
        );
        
        return view('costcentermanagement::audit.index', compact('logs'));
    }
    
    public function show(string $modelType, int $modelId)
    {
        $history = $this->auditService->getModelHistory($modelType, $modelId);
        
        return view('costcentermanagement::audit.show', compact('history', 'modelType', 'modelId'));
    }
    
    public function summary(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        
        $summary = $this->auditService->getAuditSummary(
            Carbon::parse($validated['start_date']),
            Carbon::parse($validated['end_date'])
        );
        
        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }
    
    public function export(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'required|in:csv,json',
        ]);
        
        $content = $this->auditService->exportAuditTrail(
            Carbon::parse($validated['start_date']),
            Carbon::parse($validated['end_date']),
            $validated['format']
        );
        
        $filename = sprintf(
            'audit_trail_%s_to_%s.%s',
            $validated['start_date'],
            $validated['end_date'],
            $validated['format']
        );
        
        $contentType = $validated['format'] === 'csv' 
            ? 'text/csv' 
            : 'application/json';
        
        return response($content)
            ->header('Content-Type', $contentType)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
    
    public function userActivity(Request $request, int $userId)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        
        $activity = $this->auditService->getUserActivity(
            $userId,
            Carbon::parse($validated['start_date']),
            Carbon::parse($validated['end_date'])
        );
        
        return view('costcentermanagement::audit.user-activity', compact('activity', 'userId'));
    }
}
