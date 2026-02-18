<?php

namespace Modules\CostCenterManagement\Traits;

use Modules\CostCenterManagement\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->auditEvent('created');
        });
        
        static::updated(function ($model) {
            $model->auditEvent('updated');
        });
        
        static::deleted(function ($model) {
            $model->auditEvent('deleted');
        });
    }
    
    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
    
    public function auditEvent(string $event, ?string $justification = null): void
    {
        $oldValues = null;
        $newValues = null;
        
        if ($event === 'created') {
            $newValues = $this->getAuditableAttributes();
        } elseif ($event === 'updated') {
            $oldValues = $this->getOriginal();
            $newValues = $this->getAttributes();
            
            // Filter hanya field yang berubah
            $changed = array_diff_assoc($newValues, $oldValues);
            if (empty($changed)) {
                return; // Tidak ada perubahan, skip audit
            }
            
            $oldValues = array_intersect_key($oldValues, $changed);
            $newValues = $changed;
        } elseif ($event === 'deleted') {
            $oldValues = $this->getAuditableAttributes();
        }
        
        AuditLog::create([
            'auditable_type' => get_class($this),
            'auditable_id' => $this->id,
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'justification' => $justification,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_id' => Auth::id(),
        ]);
    }
    
    public function auditCustomEvent(string $event, array $data = [], ?string $justification = null): void
    {
        AuditLog::create([
            'auditable_type' => get_class($this),
            'auditable_id' => $this->id,
            'event' => $event,
            'old_values' => $data['old'] ?? null,
            'new_values' => $data['new'] ?? null,
            'justification' => $justification,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_id' => Auth::id(),
        ]);
    }
    
    protected function getAuditableAttributes(): array
    {
        $attributes = $this->getAttributes();
        
        // Exclude fields yang tidak perlu di-audit
        $excluded = $this->auditExclude ?? ['password', 'remember_token'];
        
        return array_diff_key($attributes, array_flip($excluded));
    }
}
