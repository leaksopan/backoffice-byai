<?php

namespace Modules\MasterDataManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MdmHrAssignment extends Model
{
    protected $fillable = [
        'hr_id',
        'unit_id',
        'allocation_percentage',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'allocation_percentage' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($assignment) {
            self::validateAllocationLimit($assignment);
        });

        static::updating(function ($assignment) {
            self::validateAllocationLimit($assignment);
        });
    }

    protected static function validateAllocationLimit($assignment)
    {
        $hr = MdmHumanResource::find($assignment->hr_id);
        
        if (!$hr) {
            return;
        }

        // Check if HR is inactive
        if (!$hr->is_active) {
            throw new \Exception('Tidak dapat membuat penugasan untuk SDM yang tidak aktif');
        }

        // Calculate current total allocation
        $currentTotal = $hr->activeAssignments()
            ->where('id', '!=', $assignment->id)
            ->sum('allocation_percentage');
        
        $newTotal = $currentTotal + $assignment->allocation_percentage;
        
        if ($newTotal > 100) {
            throw new \Exception(
                'Total alokasi melebihi 100%. Sisa alokasi tersedia: ' . (100 - $currentTotal) . '%'
            );
        }
    }

    public function humanResource(): BelongsTo
    {
        return $this->belongsTo(MdmHumanResource::class, 'hr_id');
    }

    public function organizationUnit(): BelongsTo
    {
        return $this->belongsTo(MdmOrganizationUnit::class, 'unit_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->whereDate('start_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', now());
            });
    }
}
