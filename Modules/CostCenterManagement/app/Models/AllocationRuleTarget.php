<?php

namespace Modules\CostCenterManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllocationRuleTarget extends Model
{
    protected $fillable = [
        'allocation_rule_id',
        'target_cost_center_id',
        'allocation_percentage',
        'allocation_weight',
    ];

    protected $casts = [
        'allocation_percentage' => 'decimal:2',
        'allocation_weight' => 'decimal:2',
    ];

    // Relationships
    public function allocationRule(): BelongsTo
    {
        return $this->belongsTo(AllocationRule::class);
    }

    public function targetCostCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class, 'target_cost_center_id');
    }
}
