<?php

namespace Modules\CostCenterManagement\Services;

use Modules\CostCenterManagement\Models\AuditLog;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class AuditTrailService
{
    public function getAuditTrail(
        ?string $modelType = null,
        ?int $modelId = null,
        ?string $event = null,
        ?int $userId = null,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        int $perPage = 50
    ) {
        $query = AuditLog::query()
            ->with('user')
            ->orderBy('created_at', 'desc');
        
        if ($modelType && $modelId) {
            $query->forModel($modelType, $modelId);
        } elseif ($modelType) {
            $query->where('auditable_type', $modelType);
        }
        
        if ($event) {
            $query->forEvent($event);
        }
        
        if ($userId) {
            $query->byUser($userId);
        }
        
        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }
        
        return $query->paginate($perPage);
    }
    
    public function getAuditSummary(Carbon $startDate, Carbon $endDate): array
    {
        $logs = AuditLog::dateRange($startDate, $endDate)->get();
        
        return [
            'total_events' => $logs->count(),
            'by_event' => $logs->groupBy('event')->map->count(),
            'by_model' => $logs->groupBy('auditable_type')->map->count(),
            'by_user' => $logs->groupBy('user_id')->map->count(),
            'date_range' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
        ];
    }
    
    public function getModelHistory(string $modelType, int $modelId): Collection
    {
        return AuditLog::forModel($modelType, $modelId)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();
    }
    
    public function getUserActivity(int $userId, Carbon $startDate, Carbon $endDate): Collection
    {
        return AuditLog::byUser($userId)
            ->dateRange($startDate, $endDate)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    public function exportAuditTrail(
        Carbon $startDate,
        Carbon $endDate,
        string $format = 'csv'
    ): string {
        $logs = AuditLog::dateRange($startDate, $endDate)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();
        
        if ($format === 'csv') {
            return $this->exportToCsv($logs);
        } elseif ($format === 'json') {
            return $logs->toJson();
        }
        
        throw new \InvalidArgumentException("Unsupported export format: {$format}");
    }
    
    protected function exportToCsv(Collection $logs): string
    {
        $csv = "Timestamp,Event,Model Type,Model ID,User,IP Address,Changes\n";
        
        foreach ($logs as $log) {
            $changes = $this->formatChanges($log);
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s,\"%s\"\n",
                $log->created_at->toDateTimeString(),
                $log->event,
                $log->auditable_type,
                $log->auditable_id,
                $log->user ? $log->user->name : 'System',
                $log->ip_address ?? 'N/A',
                str_replace('"', '""', $changes)
            );
        }
        
        return $csv;
    }
    
    protected function formatChanges(AuditLog $log): string
    {
        $changed = $log->getChangedFields();
        
        if (empty($changed)) {
            return 'No changes';
        }
        
        $formatted = [];
        foreach ($changed as $field => $values) {
            $formatted[] = sprintf(
                "%s: %s → %s",
                $field,
                $this->formatValue($values['old']),
                $this->formatValue($values['new'])
            );
        }
        
        return implode('; ', $formatted);
    }
    
    protected function formatValue($value): string
    {
        if (is_null($value)) {
            return 'NULL';
        }
        
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        
        if (is_array($value)) {
            return json_encode($value);
        }
        
        return (string) $value;
    }
    
    public function cleanupOldLogs(int $retentionYears = 5): int
    {
        $cutoffDate = Carbon::now()->subYears($retentionYears);
        
        return AuditLog::where('created_at', '<', $cutoffDate)->delete();
    }
}
