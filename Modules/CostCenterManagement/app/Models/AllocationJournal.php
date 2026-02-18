<?php

namespace Modules\CostCenterManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class AllocationJournal extends Model
{
    protected $fillable = [
        'batch_id',
        'allocation_rule_id',
        'source_cost_center_id',
        'target_cost_center_id',
        'period_start',
        'period_end',
        'source_amount',
        'allocated_amount',
        'allocation_base_value',
        'calculation_detail',
        'status',
        'posted_at',
        'posted_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'source_amount' => 'decimal:2',
        'allocated_amount' => 'decimal:2',
        'allocation_base_value' => 'decimal:2',
        'posted_at' => 'datetime',
    ];

    // Relationships
    public function allocationRule(): BelongsTo
    {
        return $this->belongsTo(AllocationRule::class);
    }

    public function sourceCostCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class, 'source_cost_center_id');
    }

    public function targetCostCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class, 'target_cost_center_id');
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    // Scopes
    public function scopeByBatch($query, string $batchId)
    {
        return $query->where('batch_id', $batchId);
    }

    public function scopeByPeriod($query, $startDate, $endDate)
    {
        return $query->where('period_start', '>=', $startDate)
            ->where('period_end', '<=', $endDate);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    public function scopeReversed($query)
    {
        return $query->where('status', 'reversed');
    }

    public function scopeBySourceCostCenter($query, int $costCenterId)
    {
        return $query->where('source_cost_center_id', $costCenterId);
    }

    public function scopeByTargetCostCenter($query, int $costCenterId)
    {
        return $query->where('target_cost_center_id', $costCenterId);
    }

    // Accessors
    public function getCalculationDetailArrayAttribute()
    {
        return json_decode($this->calculation_detail, true);
    }
}
